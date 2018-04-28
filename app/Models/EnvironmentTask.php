<?php

/*
 * This file is part of Piplin.
 *
 * Copyright (C) 2016-2017 piplin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Piplin\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model for environment links.
 */
class EnvironmentTask extends Model
{
    protected $table = 'environment_task';

    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id', 'id');
    }
}
