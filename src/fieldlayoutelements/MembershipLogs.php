<?php
/**
 * Membership plugin for Craft CMS 5.x
 *
 * Give your users special access based on their Commerce Subscriptions.
 *
 * @link      https://oof.studio/
 * @copyright Copyright (c) 2025 oof. Studio
 */

namespace oofbar\membership\fieldlayoutelements;

use Craft;
use craft\base\ElementInterface;
use craft\fieldlayoutelements\BaseUiElement;

/**
 * Membership Logs field layout element
 *
 * Displays a list of actions the plugin has taken in response to the
 *
 * @since 3.0.0
 */
class MembershipLogs extends BaseUiElement
{
    /**
     * @inheritdoc
     */
    protected function selectorLabel(): string
    {
        return Craft::t('membership', 'Membership Logs');
    }

    /**
     * @inheritdoc
     */
    protected function selectorIcon(): ?string
    {
        return '@appicons/clock.svg';
    }

    /**
     * @inheritdoc
     */
    public function formHtml(?ElementInterface $element = null, bool $static = false): ?string
    {
        return Craft::$app->getView()->renderTemplate('membership/_fieldlayoutelements/logs', [
            'subscription' => $element,
            'canManageGrants' => Craft::$app->getUser()->getIdentity()->can('membership-manageGrants'),
        ]);
    }
}