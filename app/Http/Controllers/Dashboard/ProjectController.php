<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Piplin\Http\Controllers\Dashboard;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use McCool\LaravelAutoPresenter\Facades\AutoPresenter;
use Piplin\Bus\Jobs\SetupSkeletonJob;
use Piplin\Http\Controllers\Controller;
use Piplin\Http\Requests\StoreProjectRequest;
use Piplin\Models\Command;
use Piplin\Models\PublishVersions;
use Piplin\Models\Task;
use Piplin\Models\Project;
use Piplin\Services\Scripts\Parser as ScriptParser;
use Piplin\Services\Scripts\Runner as Process;

/**
 * The controller of projects.
 */
class ProjectController extends Controller
{
    /**
     * The details of an individual project.
     *
     * @param Project $project
     * @param string  $tab
     *
     * @return View
     */
    public function show(Project $project, $tab = '')
    {
        $optional = $project->commands->filter(function (Command $command) {
            return $command->optional;
        });

        $data = [
            'project'         => $project,
            'targetable_type' => get_class($project),
            'targetable_id'   => $project->id,
            'optional'        => $optional,
            'tasks'           => $this->getLatest($project),
            'publish_versions'=> $this->getLatestPublishVersions($project),
            'tab'             => $tab,
            'breadcrumb'      => [
                ['url' => route('projects', ['id' => $project->id]), 'label' => $project->name],
            ],
        ];

        $data['environments'] = $project->environments;
        if ($tab === 'hooks') {
            $data['hooks'] = $project->hooks;
            $data['title'] = trans('hooks.label');
        } elseif ($tab === 'members') {
            $data['members'] = $project->members->toJson();
            $data['title']   = trans('members.label');
        } elseif ($tab === 'environments') {
            $data['title'] = trans('environments.label');
        } elseif ($tab === 'publish_versions') {
            $data['title'] = trans('publish_versions.label');
        }

        return view('dashboard.projects.show', $data);
    }

    /**
    /**
     * 打更新包操作界面
     * @param Project $project
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function patches(Project $project)
    {
        $data = [
            'project' => $project,
            'tags' => $project->tags()->reverse()->all(),
            'urlCreatePatch' => route('createPatch', ['project' => $project]),
        ];
        return view('dashboard.patches.index', $data);
    }

    /**
     * 打更新包
     */
    public function createPatch(Request $request, Project $project)
    {
        $fromTag = $request->get('from');
        $toTag = $request->get('to');
        if (empty($project) || empty($fromTag) || empty($toTag)) {
            throw new \Exception('参数错误');
        }

        $private_key = tempnam(storage_path('app/'), 'sshkey');
        file_put_contents($private_key, $project->private_key_content);
        chmod($private_key, 0600);

        $wrapper = with(new ScriptParser)->parseFile('tools.SSHWrapperScript', [
            'private_key' => $private_key,
        ]);

        $wrapper_file = tempnam(storage_path('app/'), 'gitssh');
        file_put_contents($wrapper_file, $wrapper);
        chmod($wrapper_file, 0755);

        $save_relative_path = 'upload/patches/' . date('YmdHis') . random_int(1000, 9999) . '/';
        $save_path = public_path($save_relative_path);
        if (!is_dir($save_path)) {
            mkdir($save_path, 0755, true);
        }
        $patch_filename = $project->name . $fromTag . '-' . $toTag . '.tar.gz';

        $archive_path = storage_path('app/patches/' . date('YmdHis') . random_int(1000, 9999) . '/');
        if (!is_dir($archive_path)) {
            mkdir($archive_path, 0755, true);
        }

        $process = new Process('tools.CreatePatch', [
            'wrapper_file' => $wrapper_file,
            'mirror_path'  => $project->mirrorPath(),
            'repository'   => $project->repository,
            'from_tag'     => $fromTag,
            'to_tag'       => $toTag,
            'archive_path' => $archive_path,
            'save_path'    => $save_path . $patch_filename,
        ]);
        //因shell脚本中含有中文字符，所以这里应该用UTF-8编码
        $process->setEnv(['LANG' => 'en_US.UTF-8']);
        $process->run();

        unlink($wrapper_file);
        unlink($private_key);
        if (is_dir($archive_path)) {
            exec('rm -rf ' . $archive_path);
        }

        return view('dashboard.patches.download', [
            'project' => $project,
            'url' => url($save_relative_path . $patch_filename),
            'output' => $process->getOutput(),
            'errorOutput' => $process->getErrorOutput(),
        ]);
    }

    /**
     * Store a newly created project in storage.
     *
     * @param StoreProjectRequest $request
     *
     * @return Response
     */
    public function store(StoreProjectRequest $request)
    {
        $fields = $request->only(
            'name',
            'description',
            'repository',
            'branch',
            'deploy_path',
            'allow_other_branch'
        );

        $skeleton = null;

        $project = Auth::user()->personalProjects()->create($fields);

        $project->members()->attach([Auth::user()->id]);

        dispatch(new SetupSkeletonJob($project, $skeleton));

        return $project;
    }

    /**
     * Update the specified project in storage.
     *
     * @param Project             $project
     * @param StoreProjectRequest $request
     *
     * @return Response
     */
    public function update(Project $project, StoreProjectRequest $request)
    {
        $project->update($request->only(
            'name',
            'description',
            'repository',
            'branch',
            'deploy_path',
            'allow_other_branch'
        ));

        return $project;
    }

    /**
     * Remove the specified project from storage.
     *
     * @param Project $project
     *
     * @return Response
     */
    public function destroy(Project $project)
    {
        $project->delete();

        return [
            'success' => true,
        ];
    }


    /**
     * Recover the status of specified project.
     *
     * @param Project $project
     *
     * @return Response
     */
    public function recover(Project $project)
    {
        $project->status = Project::INITIAL;
        $project->save();

        return $project;
    }

    /**
     * 启用“发布版本”
     * @param Project $project
     * @param         $version_id
     * @return string
     */
    public function enableVersion(Project $project, $version_id) {
        $publishVersion = PublishVersions::find($version_id);
        $publishVersion->status = PublishVersions::ENABLED;
        $publishVersion->save();
        return "启用成功";
    }

    /**
     * 禁用“发布版本”
     * @param Project $project
     * @param         $version_id
     * @return string
     */
    public function disableVersion(Project $project, $version_id) {
        $publishVersion = PublishVersions::find($version_id);
        $publishVersion->status = PublishVersions::DISABLED;
        $publishVersion->save();
        return "禁用成功";
    }

    /**
     * Gets the latest deployments for a project.
     *
     * @param  Project $project
     * @param  int     $paginate
     * @return array
     */
    private function getLatest(Project $project, $paginate = 15)
    {
        return Task::where('project_id', $project->id)
            ->with('user')
            ->whereNotNull('started_at')
            ->orderBy('started_at', 'DESC')
            ->paginate($paginate);
    }

    /**
     * Gets the latest publish versions for a project.
     *
     * @param  Project $project
     * @param  int     $paginate
     * @return array
     */
    private function getLatestPublishVersions(Project $project, $paginate = 15)
    {
        return PublishVersions::where('project_id', $project->id)
                           ->orderBy('id', 'DESC')
                           ->paginate($paginate);
    }
}
