<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Route::post('api/projects', [
    'uses' => 'Api\ProjectController@show',
]);

Route::get('api/project/get_latest_version/{project_id}/{env_id}', [
    'uses' => 'Api\ProjectController@getLatestVersion',
]);

Route::get('api/project/get_available_list/{project_id}/{env_id}', [
    'uses' => 'Api\ProjectController@getAvailableList',
]);
