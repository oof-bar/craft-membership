<?php
/**
 * Membership plugin for Craft CMS 4.x
 *
 * Give your users special access based on their Commerce Subscriptions.
 *
 * @link      https://oof.studio/
 * @copyright Copyright (c) 2023 oof. Studio
 */

namespace oofbar\membership;

use Craft;
use craft\base\Plugin;
use craft\events\RegisterUrlRulesEvent;
use craft\helpers\UrlHelper;
use craft\web\UrlManager;
use craft\web\twig\variables\CraftVariable;

use craft\commerce\events\SubscriptionEvent;
use craft\commerce\events\SubscriptionSwitchPlansEvent;
use craft\commerce\services\Subscriptions;

use yii\base\Event;

use oofbar\membership\services\Grants;
use oofbar\membership\services\Logs;
use oofbar\membership\services\Permissions;
use oofbar\membership\web\twig\CraftVariableBehavior;

/**
 * Membership Plugin
 * 
 * This plugin is really simple, and I hope if you're reading through the source
 * that it might encourage you to implement some
 *
 * @author    oof. Studio
 * @package   Membership
 * @since     1.0.0
 */
class Membership extends Plugin
{
    /**
     * @var string
     */
    public string $schemaVersion = '1.0.0';

    /**
     * @var bool
     */
    public bool $hasCpSettings = true;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->setComponents([
            'grants' => Grants::class,
            'logs' => Logs::class,
            'permissions' => Permissions::class,
        ]);

        // Attach plugin to the Craft template variable

        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function(Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;

                $variable->attachBehavior('membership', CraftVariableBehavior::class);
            }
        );

        // Add CP Routes

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function(RegisterUrlRulesEvent $event) {
                $event->rules['membership'] = ['template' => 'membership/index'];
                $event->rules['membership/grants'] = 'membership/grants/index';
                $event->rules['membership/grants/new'] = 'membership/grants/edit';
                $event->rules['membership/grants/<grantId:\d+>'] = 'membership/grants/edit';
            }
        );

        // Listen for key Subscription events

        Event::on(
            Subscriptions::class,
            Subscriptions::EVENT_AFTER_CREATE_SUBSCRIPTION,
            function(SubscriptionEvent $event) {
                Membership::getInstance()->getPermissions()->grantPermissionsForSubscription($event->subscription);
            }
        );

        Event::on(
            Subscriptions::class,
            Subscriptions::EVENT_AFTER_EXPIRE_SUBSCRIPTION,
            function(SubscriptionEvent $event) {
                Membership::getInstance()->getPermissions()->revokePermissionsForSubscription($event->subscription);
            }
        );

        Event::on(
            Subscriptions::class,
            Subscriptions::EVENT_AFTER_SWITCH_SUBSCRIPTION_PLAN,
            function(SubscriptionSwitchPlansEvent $event) {
                // Revoke old permissions:
                Membership::getInstance()->getPermissions()->revokePermissionsForSubscription($event->subscription, $event->oldPlan);

                // Grant new permissions:
                Membership::getInstance()->getPermissions()->grantPermissionsForSubscription($event->subscription, $event->newPlan);
            }
        );

        // Display log messages with the relevant subscriptions

        $view = Craft::$app->getView();

        $view->hook('cp.commerce.subscriptions.edit.content', function (array &$context) use ($view) {
            return $view->renderTemplate('membership/_hooks/cp.commerce.subscriptions.edit', $context);
        });
    }

    /**
     * @inheritdoc
     */
    public function getSettingsResponse(): mixed
    {
        return Craft::$app->controller->redirect(UrlHelper::cpUrl('membership/grants'));
    }

    /**
     * @inheritdoc
     */
    protected function settingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate('membership/index');
    }

    /**
     * Returns the Grants service/component.
     * 
     * @return Grants
     */
    public function getGrants(): Grants
    {
        return $this->get('grants');
    }

    /**
     * Returns the Logs service/component.
     * 
     * @return Logs
     */
    public function getLogs(): Logs
    {
        return $this->get('logs');
    }

    /**
     * Returns the Permissions service/component.
     * 
     * @return Permissions
     */
    public function getPermissions(): Permissions
    {
        return $this->get('permissions');
    }
}
