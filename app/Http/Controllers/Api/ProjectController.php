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
use Illuminate\Support\Facades\Response;
use Piplin\Http\Controllers\Controller;
use Piplin\Models\EnvironmentTask;
use Piplin\Models\Project;
use Piplin\Models\PublishVersions;

/**
 * The project controller.
 */
class ProjectController extends Controller
{
    /**
     * The details of an individual project.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function show(Request $request)
    {
        $project_id = $request->get('project_id');

        $project = Project::findOrFail($project_id);

        return $project;
    }

    /**
     * 获取最新的发布版本
     *
     * @param int $project_id
     * @param int $environment_id
     *
     * @return array
     */
    public function getLatestVersion($project_id, $environment_id)
    {

        $version = PublishVersions::where('project_id', $project_id)
            ->where('status', PublishVersions::ENABLED)
            ->orderBy('id', 'desc')
            ->first();
        $version->update_url = url('deploy/' . $version->version_hash . '/' . $environment_id);

        $response = [
            'status' => 'success',
            'data'   => $version,
        ];

        return $response;
    }

    /**
     * 获取可用的更新列表
     * @param $project_id
     * @param $environment_id
     * @return array
     */
    public function getAvailableList($project_id, $environment_id)
    {
        $lastVersionId = 0;
        $lastEnvironmentTask = EnvironmentTask::where('environment_id', $environment_id)
            ->orderBy('id', 'desc')
            ->first();
        if (!empty($lastEnvironmentTask)) {
            $lastCommit = $lastEnvironmentTask->task->commit;
            $lastVersion = PublishVersions::where('project_id', $project_id)
                ->where('status', PublishVersions::ENABLED)
                ->where('commit', $lastCommit)
                ->orderBy('id', 'desc')
                ->first();
            if (!empty($lastVersion)) {
                $lastVersionId = $lastVersion->id;
            }
        }

        $versions = PublishVersions::where('project_id', $project_id)
            ->where('status', PublishVersions::ENABLED)
            ->where('id', '>=', $lastVersionId)
            ->orderBy('id', 'desc')
            ->limit(20)
            ->get();

        foreach ($versions as $version) {
            $version->update_url = url('deploy/' . $version->version_hash . '/' . $environment_id);
        }

        $response = [
            'status' => 'success',
            'data'   => $versions,
        ];

        return $response;
    }
}
