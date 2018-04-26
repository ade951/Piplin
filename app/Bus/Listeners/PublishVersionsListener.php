<?php

namespace Piplin\Bus\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\InteractsWithQueue;
use Piplin\Bus\Events\TaskFinishedEvent;
use Piplin\Models\PublishVersions;

/**
 * 任务结束时增加发布版本，状态为待发布
 */
class PublishVersionsListener implements ShouldQueue
{
    use InteractsWithQueue, DispatchesJobs;

    /**
     * Handle the event.
     *
     * @param  TaskFinishedEvent $event
     * @return void
     */
    public function handle(TaskFinishedEvent $event)
    {
        $task    = $event->task;
        $project = $event->task->project;

        if ($task->isSuccessful()) {
            $publishVersions = new PublishVersions();
            $publishVersions->project_id = $project->id;
            $publishVersions->version_name = date('YmdHi');
            $publishVersions->version_hash = uniqid();
            $publishVersions->description = $task->reason;
            $publishVersions->status = PublishVersions::PENDING;
            $publishVersions->save();
        }
    }
}
