<?php

namespace Piplin\Models;

use Illuminate\Database\Eloquent\Model;
use McCool\LaravelAutoPresenter\HasPresenter;
use Piplin\Presenters\PublishVersionsPresenter;

/**
 * 发布版本表（商户可见的发布）
 */
class PublishVersions extends Model implements HasPresenter
{
    const PENDING  = 0; //待发布
    const ENABLED  = 1; //启用
    const DISABLED = 2; //禁用

    /**
     * Get the presenter class.
     *
     * @return string
     */
    public function getPresenterClass()
    {
        return PublishVersionsPresenter::class;
    }
}
