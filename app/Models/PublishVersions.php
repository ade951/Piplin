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
     * The attributes that are mass assignable.
     *
     * @var array
     */
    //protected $fillable = [];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['updated_at', 'deleted_at'];

    /**
     * Get the presenter class.
     *
     * @return string
     */
    public function getPresenterClass()
    {
        return PublishVersionsPresenter::class;
    }

    /**
     * Get the project associated with the version.
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function project() {
        return $this->hasOne(Project::class, 'id', 'project_id');
    }
}
