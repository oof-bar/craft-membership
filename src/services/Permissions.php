<?php
/**
 * Membership plugin for Craft CMS 4.x
 *
 * Give your users special access based on their Commerce Subscriptions.
 *
 * @link      https://oof.studio/
 * @copyright Copyright (c) 2023 oof. Studio
 */

namespace oofbar\membership\services;

use Craft;
use craft\base\Component;
use craft\helpers\ArrayHelper;

use craft\commerce\elements\Subscription;
use craft\commerce\base\Plan;

use oofbar\membership\Membership;
use oofbar\membership\events\GrantPermissionEvent;
use oofbar\membership\events\RevokePermissionEvent;
use oofbar\membership\models\Message;

/**
 * Permissions Service
 *
 * Core functionality for granting and revoking permissions based on Subscription events.
 *
 * @author    oof. Studio
 * @package   Membership
 * @since     1.0.0
 */
class Permissions extends Component
{
    /**
     * @event oofbar\membership\events\GrantPermission
     */
    public const EVENT_BEFORE_GRANT_PERMISSION = 'beforeGrantPermission';

    /**
     * @event oofbar\membership\events\RevokePermission
     */
    public const EVENT_BEFORE_REVOKE_PERMISSION = 'beforeRevokePermission';

    /**
     * Grants permissions for the passed Subscription.
     * 
     * The `$plan` argument is used to override the default behavior, which uses the currently-associated Plan to determine the grants. In situations where we need to swap out permissions (i.e. switching plans), the Subscription will only have access to the new (or old) Plan, meaning we have to offer a bit of help.
     * 
     * @param Subscription $subscription
     * @param Plan $plan
     * @return bool
     */
    public function grantPermissionsForSubscription(Subscription $subscription, Plan $plan = null): bool
    {
        $membership = Membership::getInstance();
        $logger = $membership->getLogs();

        $plan = $plan ?? $subscription->getPlan();
        $owner = $subscription->getSubscriber();

        $grants = $membership->getGrants()->getGrantsForPlan($plan);

        foreach ($grants as $grant) {
            $group = $grant->getUserGroup();

            // Disabled grants don't affect permissions:
            if (!$grant->enabled) {
                continue;
            }

            // They may already be in it:
            if ($owner->isInGroup($grant->userGroupId)) {
                $logger->log(new Message([
                    'message' => Craft::t('membership', 'The owner already belonged to group ID #{groupId} ({groupName}) when they signed up for {planName}.', [
                        'groupId' => $group->id,
                        'groupName' => $group->name,
                        'planName' => $plan->name,
                    ]),
                    'grantId' => $grant->id,
                    'subscriptionId' => $subscription->id,
                ]));

                continue;
            }

            // Fire an event to give the system a chance to alter the behavior:
            $event = new GrantPermissionEvent([
                'grant' => $grant
            ]);

            $this->trigger(self::EVENT_BEFORE_GRANT_PERMISSION, $event);

            // If it was prevented, log a message and continue:
            if (!$event->isValid) {
                $logger->log(new Message([
                    'message' => Craft::t('membership', 'A plugin prevented the owner from being added to User Group ID #{groupId}.', ['groupId' => $grant->userGroupId]),
                    'grantId' => $grant->id,
                    'subscriptionId' => $subscription->id,
                ]));

                continue;
            }

            // Get current groups, and append the granted one:
            $ownerGroups = $owner->getGroups();
            $ownerGroups[] = $group;

            // Assign by plucking their IDs:
            Craft::$app->getUsers()->assignUserToGroups($owner->id, ArrayHelper::getColumn($ownerGroups, 'id'));

            // Set them back on the User, clearing the cached values:
            $owner->setGroups($ownerGroups);

            $logger->log(new Message([
                'message' => Craft::t('membership', 'Added the owner to User Group ID #{groupId} ({groupName}) when they signed up for {planName}.', [
                    'groupId' => $group->id,
                    'groupName' => $group->name,
                    'planName' => $plan->name,
                ]),
                'grantId' => $grant->id,
                'subscriptionId' => $subscription->id,
            ]));
        }

        return true;
    }

    /**
     * Revokes permissions based on the Mermbership's Pass settings.
     * 
     * As with the sister `grant` method, this one accepts a sort of "override" for the default behavior of using the Plan attached to the Subscription. In cases where a User is switching to a new Plan, we would rather explicitly pass which Plan they're moving *away from* or *to*.
     * 
     * @param Subscription $subscription
     * @param Plan $plan
     * @return bool
     */
    public function revokePermissionsForSubscription(Subscription $subscription, Plan $plan = null): bool
    {
        $membership = Membership::getInstance();
        $logger = $membership->getLogs();

        $plan = $plan ?? $subscription->getPlan();
        $owner = $subscription->getSubscriber();

        // Get the Grants we'll try and revoke:
        $grants = $membership->getGrants()->getGrantsForPlan($plan);

        // Get the User's *other* Subscriptions, if any:
        $subscriptions = Subscription::find()
            ->user($owner)
            ->id(['not', $subscription->id])
            ->all();

        // Fetch the Grants for those Subscriptions...
        $protectedGrants = $membership->getGrants()->getGrants([
            'planId' => ArrayHelper::getColumn($subscriptions, 'planId')
        ]);

        // ...and pull the “protected” UserGroup IDs off of those Grants:
        $protectedGroupIds = ArrayHelper::getColumn($protectedGrants, 'userGroupId');

        foreach ($grants as $grant) {
            $group = $grant->getUserGroup();

            // Disabled grants don't affect permissions:
            if (!$grant->enabled) {
                $logger->log(new Message([
                    'message' => Craft::t('membership', 'The owner was’t removed from Group ID #{groupId} ({groupName}) because the grant was disabled.', [
                        'groupId' => $group->id,
                        'groupName' => $group->name
                    ]),
                    'grantId' => $grant->id,
                    'subscriptionId' => $subscription->id
                ]));

                continue;
            }

            // We also don't want to revoke a permission granted by a different (active) Subscription:
            if (in_array($group->id, $protectedGroupIds)) {
                $logger->log(new Message([
                    'message' => Craft::t('membership', 'Another active Subscription prevented the owner from being removed from Group ID #{groupId}.', ['groupId' => $group->id]),
                    'grantId' => $grant->id,
                    'subscriptionId' => $subscription->id
                ]));

                continue;
            }

            // They may not be in it:
            if (!$owner->isInGroup($group->id)) {
                $logger->log(new Message([
                    'message' => Craft::t('membership', 'The owner wasn’t in User Group ID #{groupId} ({groupName}), so no action was taken.', [
                        'groupId' => $group->id,
                        'groupName' => $group->name
                    ]),
                    'grantId' => $grant->id,
                    'subscriptionId' => $subscription->id
                ]));

                continue;
            }

            // Fire an event to give the system a chance to alter the behavior:
            $event = new RevokePermissionEvent([
                'grant' => $grant
            ]);

            $this->trigger(self::EVENT_BEFORE_REVOKE_PERMISSION, $event);

            // If it was prevented, log a message and continue:
            if (!$event->isValid) {
                $logger->log(new Message([
                    'message' => Craft::t('membership', 'A plugin prevented the owner from being removed from User Group ID #{groupId} ({groupName}).', [
                        'groupId' => $group->id,
                        'groupName' => $group->name
                    ]),
                    'grantId' => $grant->id,
                    'subscriptionId' => $subscription->id
                ]));

                continue;
            }

            // Get current groups, and filter out this one:
            $newGroups = array_filter($owner->getGroups(), function ($g) use ($group) {
                if ($g->id === $group->id) {
                    return false;
                }

                return true;
            });

            // Assign the new groups:
            Craft::$app->getUsers()->assignUserToGroups($owner->id, ArrayHelper::getColumn($newGroups, 'id'));

            // Set them back on the User, clearing the cached values:
            $owner->setGroups($newGroups);

            $logger->log(new Message([
                'message' => Craft::t('membership', 'Removed the owner from User Group ID #{groupId} ({groupName}).', [
                    'groupId' => $group->id,
                    'groupName' => $group->name
                ]),
                'grantId' => $grant->id,
                'subscriptionId' => $subscription->id
            ]));
        }

        return true;
    }

    /**
     * Compares a list of UserGroups and returns anything present in both sets.
     * 
     * @param UserGroup[] $a
     * @param UserGroup[] $b
     * @return UserGroup[] Overlap between the sets
     */
    private function _getGroupsOverlap(array $a, array $b): array
    {
        $overlap = [];
        $bIds = ArrayHelper::getColumn($b, 'id');

        foreach ($a as $group) {
            if (in_array($group->id, $b)) {
                $overlap[] = $group;
            }
        }

        return $overlap;
    }
}
