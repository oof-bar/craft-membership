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
 * Base Admin Controller
 *
 * Enforces Admin access, and passes on the BaseController methods.
 *
 * @author    oof. Studio
 * @package   Membership
 * @since     1.0.0
 */
class BaseAdminController extends BaseController
{
    /**
     * @inheritdoc
     */
    public function beforeAction($action): bool
    {
        $this->requireAdmin();

        return parent::beforeAction($action);
    }
}