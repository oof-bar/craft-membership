<?php
/**
 * Membership plugin for Craft CMS 3.x
 *
 * Give your users special access based on their Commerce Subscriptions.
 *
 * @link      https://oof.studio/
 * @copyright Copyright (c) 2020 oof. Studio
 */

namespace oofbar\membership;

use Craft;
use craft\base\Plugin;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterUserPermissionsEvent;
use craft\helpers\UrlHelper;
use craft\services\UserPermissions;
use craft\web\UrlManager;
use craft\web\twig\variables\CraftVariable;

use craft\commerce\Plugin as Commerce;
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
    public $version = '1.0.0';

    /**
     * @var string
     */
    public string $schemaVersion = '1.0.0';

    /**
     * @var bool
     */
    public bool $hasCpSettings = true;

    /**
     * Set our $plugin static property to this class so that it can be accessed via
     * Membership::$plugin
     *
     * Called after the plugin class is instantiated; do any one-time initialization
     * here such as hooks and events.
     *
     * If you have a '/vendor/autoload.php' file, it will be loaded for you automatically;
     * you do not need to load it in your init() method.
     *
     */
    public function init()
    {
        parent::init();

        $this->setComponents([
            'grants' => Grants::class,
            'logs' => Logs::class,
            'permissions' => Permissions::class,
        ]);

        // Attach plugin to the Craft template variable:
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;

                $variable->attachBehavior('membership', CraftVariableBehavior::class);
            });

        // Add CP Routes
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['membership'] = ['template' => 'membership/index'];
                $event->rules['membership/grants'] = 'membership/grants/index';
                $event->rules['membership/grants/new'] = 'membership/grants/edit';
                $event->rules['membership/grants/<grantId:\d+>'] = 'membership/grants/edit';
            });

        // Register Permissions
        Event::on(
            UserPermissions::class,
            UserPermissions::EVENT_REGISTER_PERMISSIONS,
            function (RegisterUserPermissionsEvent $event) {
                $event->permissions[Craft::t('membership', 'Membership')] = [
                    'membership-manageGrants' => ['label' => Craft::t('membership', 'Manage Grants')],
                    'membership-viewLogs' => ['label' => Craft::t('membership', 'View Logs')],
                ];
            });

        // Listen for key Subscription events:
        Event::on(
            Subscriptions::class,
            Subscriptions::EVENT_AFTER_CREATE_SUBSCRIPTION,
            function (SubscriptionEvent $event) {
                Membership::getInstance()->permissions->grantPermissionsForSubscription($event->subscription);
            });

        Event::on(
            Subscriptions::class,
            Subscriptions::EVENT_AFTER_EXPIRE_SUBSCRIPTION,
            function (SubscriptionEvent $event) {
                Membership::getInstance()->permissions->revokePermissionsForSubscription($event->subscription);
            });

        Event::on(
            Subscriptions::class,
            Subscriptions::EVENT_AFTER_SWITCH_SUBSCRIPTION_PLAN,
            function (SubscriptionSwitchPlansEvent $event) {
                // Revoke old permissions:
                Membership::getInstance()->permissions->revokePermissionsForSubscription($event->subscription, $event->oldPlan);

                // Grant new permissions:
                Membership::getInstance()->permissions->grantPermissionsForSubscription($event->subscription, $event->newPlan);
            });

        // Display log messages with the relevant subscriptions:
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
}
