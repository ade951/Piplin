<?php

namespace Piplin\Presenters;

use Piplin\Models\PublishVersions;

/**
 * The view presenter for a PublishVersions class.
 */
class PublishVersionsPresenter extends CommandPresenter
{

    /**
     * Gets the CSS class for the version status.
     *
     * @return string
     */
    public function css_class()
    {
        if ($this->wrappedObject->status === PublishVersions::ENABLED) {
            return 'success';
        } elseif ($this->wrappedObject->status === PublishVersions::PENDING) {
            return 'pending';
        } elseif ($this->wrappedObject->status === PublishVersions::DISABLED) {
            return 'danger';
        }

        return 'default';
    }

    /**
     * Gets the translated version status string.
     *
     * @return string
     */
    public function readable_status()
    {
        if ($this->wrappedObject->status === PublishVersions::PENDING) {
            return trans('publish_versions.pending');
        } elseif ($this->wrappedObject->status === PublishVersions::ENABLED) {
            return trans('publish_versions.enabled');
        } elseif ($this->wrappedObject->status === PublishVersions::DISABLED) {
            return trans('publish_versions.disabled');
        }

        return trans('publish_versions.unknown');
    }

}
