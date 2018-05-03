<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Piplin\Http\Controllers\Api;

use Illuminate\Http\Request;
use Piplin\Bus\Jobs\AbortTaskJob;
use Piplin\Bus\Jobs\CreateTaskJob;
use Piplin\Bus\Jobs\TriggerGitUpdateJob;
use Piplin\Http\Controllers\Controller;
use Piplin\Models\Command;
use Piplin\Models\PublishVersions;
use Piplin\Models\Task;
use Piplin\Models\Project;
use Piplin\Services\Webhooks\Beanstalkapp;
use Piplin\Services\Webhooks\Bitbucket;
use Piplin\Services\Webhooks\Custom;
use Piplin\Services\Webhooks\Github;
use Piplin\Services\Webhooks\Gitlab;
use Piplin\Services\Webhooks\Gogs;
use Piplin\Services\Webhooks\Oschina;

/**
 * The task incoming-webhook controller.
 */
class IncomingWebhookController extends Controller
{
    /**
     * List of supported service classes.
     * @var array
     */
    private $services = [
        Beanstalkapp::class,
        Bitbucket::class,
        Github::class,
        Gitlab::class,
        Gogs::class,
        Oschina::class,
    ];

    /**
     * Class constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $this->services[] = Custom::class;
    }

    /**
     * 通过Webhook部署某个环境
     *
     * @param Request $request
     * @param int     $env 部署环境id
     * @param string  $versionHash 发布版本的哈希值
     *
     * @return Response
     */
    public function deploy(Request $request, string $versionHash, int $env = 0)
    {
        if ($env == 0) {
            return ['success' => '请选择环境'];
        }
        //TODO 权限控制：拿Token去项目后台比对

        $publishVersion = PublishVersions::where('version_hash', $versionHash)
            ->where('status', PublishVersions::ENABLED)
            ->firstOrFail();

        $project = $publishVersion->project;

        $deployPlan = $project->deployPlan;

        $success = false;
        if ($deployPlan && $deployPlan->environments->count() > 0) {

            $request['reason'] = $publishVersion->reason;
            $request['project_id'] = $project->id;
            $request['environments'] = $env;
            $request['source'] = 'commit';
            $request['branch'] = $publishVersion->branch;
            $request['commit'] = $publishVersion->commit;
            $request['source_commit'] = $publishVersion->commit;

            //选择默认选中的可选命令
            $commands = [];
            $deployPlan->commands->filter(function (Command $command) use (&$commands) {
                if ($command->optional && $command->default_on) {
                    $commands[] = $command->id;
                }
                return $command->optional;
            });
            $request['commands'] = $commands;

            $payload = $this->parseWebhookRequest($request, $project);

            if (is_array($payload) && ($project->allow_other_branch || $project->branch === $payload['branch'])) {
                $this->abortQueued($project->id);
                $payload['targetable_type'] = get_class($deployPlan);
                $payload['targetable_id'] = $deployPlan->id;
                dispatch(new CreateTaskJob($project, $payload));

                $success = true;
            }
        }

        return [
            'success' => $success,
        ];
    }

    /**
     * Handles incoming requests to trigger build.
     *
     * @param Request $request
     * @param string  $hash
     *
     * @return Response
     */
    public function build(Request $request, $hash)
    {
        $project = Project::where('hash', $hash)->firstOrFail();

        $buildPlan = $project->buildPlan;

        $success = false;
        if ($buildPlan && $buildPlan->servers->count() > 0) {
            $payload = $this->parseWebhookRequest($request, $project);

            if (is_array($payload) && ($project->allow_other_branch || $project->branch === $payload['branch'])) {
                $this->abortQueued($project->id);
                $payload['targetable_type'] = get_class($buildPlan);
                $payload['targetable_id'] = $buildPlan->id;
                dispatch(new CreateTaskJob($project, $payload));

                $success = true;
            }
        }

        return [
            'success' => $success,
        ];
    }

    /**
     * 当git有新的提交时，触发更新仓库信息（需要把此接口URL配置到git仓库的WebHooks列表中）
     * 此接口gitee.com专用，配置时把password配为本系统中对应的project_id
     * @param Request $request
     * @return mixed
     */
    public function giteePushed(Request $request)
    {
        //gitee.com的hooks都有带password字段，我们在配置时统一配置为各项目的id
        if (!$request->has('password') || !is_numeric($request->get('password'))) {
            return [
                'code' => -1,
                'message' => '参数错误',
            ];
        }
        $projectId = $request->get('password');
        $project = Project::find($projectId);
        $this->dispatch(new TriggerGitUpdateJob($project));
    }

    /**
     * Goes through the various webhook integrations as checks if the request is for them and parses it.
     * Then adds the various additional details required to trigger a task.
     *
     * @param Request $request
     * @param Project $project
     *
     * @return mixed Either an array of parameters for the task config, or false if it is invalid.
     */
    private function parseWebhookRequest(Request $request, Project $project)
    {
        foreach ($this->services as $service) {
            $integration = new $service($request);

            if ($integration->isRequestOrigin()) {
                return $this->appendProjectSettings($integration->handlePush(), $request, $project);
            }
        }

        return false;
    }

    /**
     * Takes the data returned from the webhook request and then adds projects own data, such as project ID
     * and runs any checks such as checks the branch is allowed to be deployed.
     *
     * @param mixed   $payload
     * @param Request $request
     * @param Project $project
     *
     * @return mixed Either an array of the complete task config, or false if it is invalid.
     */
    private function appendProjectSettings($payload, Request $request, Project $project)
    {
        // If the payload is empty return false
        if (!is_array($payload) || !count($payload)) {
            return false;
        }

        $payload['project_id'] = $project->id;

        // If there is no branch set get it from the project
        if (is_null($payload['branch']) || empty($payload['branch'])) {
            $payload['branch'] = $project->branch;
        }

        // If the project doesn't allow other branches check the requested branch is the correct one
        if (!$project->allow_other_branch && $payload['branch'] !== $project->branch) {
            return false;
        }

        $payload['payload'] = $request->only(['source', "source_{$request->get('source')}"]);
        if ($request->get('source') == 'commit') {
            $payload['commit'] = $request->get('source_commit');
        }

        $payload['optional'] = [];

        // Check if the commands input is set, if so explode on comma and filter out any invalid commands
        if ($request->has('commands')) {
            $valid     = $project->deployPlan->commands->pluck('id');
            $requested = $request->get('commands');

            $payload['optional'] = collect($requested)->unique()
                                                      ->intersect($valid)
                                                      ->toArray();
        }

        $payload['environments'] = [];
        if ($request->has('environments')) {
            $valid     = $project->deployPlan->environments->pluck('id');
            $requested = explode(',', $request->get('environments'));

            $payload['environments'] = collect($requested)->unique()
                                                      ->intersect($valid)
                                                      ->toArray();
        }

        // Check if the request has an update_only query string and if so check the branch matches
        if ($request->has('update_only') && $request->get('update_only') !== false) {
            $task = Task::where('project_id', $project->id)
                           ->where('status', Task::COMPLETED)
                           ->whereNotNull('started_at')
                           ->orderBy('started_at', 'DESC')
                           ->first();

            if (!$task || $task->branch !== $payload['branch']) {
                return false;
            }
        }

        return $payload;
    }

    /**
     * Gets all pending and running tasks for a project and aborts them.
     *
     * @param int $project_id
     *
     * @return void
     */
    private function abortQueued($project_id)
    {
        $tasks = Task::where('project_id', $project_id)
                                   ->whereIn('status', [Task::RUNNING, Task::PENDING])
                                   ->orderBy('started_at', 'DESC')
                                   ->get();

        foreach ($tasks as $task) {
            $task->status = Task::ABORTING;
            $task->save();

            dispatch(new AbortTaskJob($task));

            if ($task->is_webhook) {
                $task->delete();
            }
        }
    }
}
