<?php
/**
 * Membership plugin for Craft CMS 3.x
 *
 * Give your users special access based on their Commerce Subscriptions.
 *
 * @link      https://oof.studio/
 * @copyright Copyright (c) 2020 oof. Studio
 */

namespace oofbar\membership\events;

use craft\events\CancelableEvent;

/**
 * Revoke Permission Event
 * 
 * Raised whenever a permission is about to be revoked in response to a Subscription expiring.
 *
 * @author    oof. Studio
 * @package   Membership
 * @since     1.0.0
 */
class RevokePermissionEvent extends CancelableEvent
{
    /**
     * @var Grant The model containing information about the permission that is about to be revoked.
     */
    public $grant;
}
