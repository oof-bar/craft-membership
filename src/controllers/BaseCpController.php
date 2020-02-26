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

use Craft;

/**
 * Base CP Controller
 *
 * Enforces CP access privilege, and passes on the BaseController methods.
 *
 * @author    oof. Studio
 * @package   Membership
 * @since     1.0.0
 */
class BaseCpController extends BaseController
{
    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $this->requirePermission('accessCp');

        return parent::beforeAction($action);
    }
}