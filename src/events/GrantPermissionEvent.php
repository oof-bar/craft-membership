<?php
/**
 * Membership plugin for Craft CMS 4.x
 *
 * Give your users special access based on their Commerce Subscriptions.
 *
 * @link      https://oof.studio/
 * @copyright Copyright (c) 2023 oof. Studio
 */

namespace oofbar\membership\events;

use craft\events\CancelableEvent;
use oofbar\membership\models\Grant;

/**
 * Grant Permission Event
 * 
 * Raised whenever a permission is granted in response to activating a Subscription.
 *
 * @author    oof. Studio
 * @package   Membership
 * @since     1.0.0
 */
class GrantPermissionEvent extends CancelableEvent
{
    /**
     * @var Grant The model containing information about the permission that is about to be granted.
     */
    public Grant $grant;
}
