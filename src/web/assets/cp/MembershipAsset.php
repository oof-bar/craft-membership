<?php
/**
 * Membership plugin for Craft CMS 4.x
 *
 * Give your users special access based on their Commerce Subscriptions.
 *
 * @link      https://oof.studio/
 * @copyright Copyright (c) 2023 oof. Studio
 */

namespace oofbar\membership\web\assets\cp;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;
use craft\web\View;

use yii\web\JqueryAsset;

/**
 * AssetBundle for the Memberhip plugin's Control Panel functionality.
 *
 * @author    oof. Studio
 * @package   Membership
 * @since     1.0.0
 */
class MembershipAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = __DIR__ . '/dist';

        $this->depends = [
            CpAsset::class,
            JqueryAsset::class,
        ];

        $this->css = [
            'css/Membership.css'
        ];

        $this->js = [
            'js/Membership.js'
        ];

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function registerAssetFiles($view)
    {
        parent::registerAssetFiles($view);

        if ($view instanceof View) {
            $view->registerTranslations('app', [
                'New {eventType}',
                'Update Application Status',
                'Message',
                'Status change message',
                'Update',
                'Cancel',
                'New',
                'Edit',
                'Add',
                'Update',
            ]);
        }
    }
}
