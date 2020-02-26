<?php
/**
 * Membership plugin for Craft CMS 3.x
 *
 * Give your users special access based on their Commerce Subscriptions.
 *
 * @link      https://oof.studio/
 * @copyright Copyright (c) 2020 oof. Studio
 */

namespace oofbar\membership\controllers;

/**
 * Logs Controller
 *
 * @author    oof. Studio
 * @package   Membership
 * @since     1.0.0
 */
class LogsController extends BaseAdminController
{
    /**
     * Renders the most recent logs.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $this->renderTemplate('membership/logs/index');
    }
}
