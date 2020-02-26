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

use craft\commerce\Plugin as Commerce;

use yii\web\HttpException;

use oofbar\membership\Membership;
use oofbar\membership\models\Grant;

/**
 * Grants Controller
 *
 * This controller is only accessible to Admin users. Due to the nature of the grants system, it can be used to significantly elevate a user's permissions.
 *
 * @author    oof. Studio
 * @package   Membership
 * @since     1.0.0
 */
class GrantsController extends BaseAdminController
{
    /**
     * Displays a list of Grants to an administrator
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $grants = Membership::getInstance()->grants->getAllGrants();

        $this->renderTemplate('membership/grants/index', compact('grants'));
    }

    /**
     * Renders a new Grant form, or re-renders an in-progress one.
     *
     * @return mixed
     */
    public function actionEdit(int $grantId = null, Grant $grant = null)
    {
        $plans = Commerce::getInstance()->getPlans()->getAllPlans();
        $userGroups = Craft::$app->getUserGroups()->getAllGroups();

        if ($grant) {
            // Nothing to do, if one was passed via route params!
        } else if (!is_null($grantId)) {
            $grant = Membership::getInstance()->grants->getGrantById($grantId);

            if (!$grant) {
                throw new HttpException(404, Craft::t('membership', 'The grant does not exist.'));
            }
        } else {
            $grant = new Grant;
        }

        return $this->renderTemplate('membership/grants/edit', compact('grant', 'plans', 'userGroups'));
    }

    /**
     * Save a new or existing Grant
     *
     * @return mixed
     */
    public function actionSave()
    {
        $this->requirePostRequest();
        $this->requireAdmin();

        $request = Craft::$app->getRequest();

        $id = (int)$request->getBodyParam('id');

        if ($id) {
            $grant = Membership::getInstance()->grants->getGrantById($id);

            if (!$grant) {
                throw new HttpException(404, Craft::t('membership', 'The grant does not exist.'));
            }
        } else {
            $grant = new Grant;
        }

        $grant->name = $request->getBodyParam('name', $grant->name);
        $grant->planId = (int)$request->getBodyParam('planId', $grant->planId);
        $grant->userGroupId = (int)$request->getBodyParam('userGroupId', $grant->userGroupId);
        $grant->enabled = (bool)$request->getBodyParam('enabled', $grant->enabled);

        if (!Membership::getInstance()->grants->saveGrant($grant)) {
            return $this->_sendErrorResponse(Craft::t('membership', 'Failed to save grant.'), compact('grant'));
        }

        return $this->_sendSuccessResponse(Craft::t('membership', 'Grant saved.'));
    }

    /**
     * Delete an existing Grant
     *
     * @return mixed
     */
    public function actionDelete()
    {
        $grantId = Craft::$app->getRequest()->getRequiredBodyParam('id');

        $membership = Membership::getInstance();

        if (!$grantId) {
            return $this->_sendErrorResponse(Craft::t('membership', 'No ID was provided'));
        }

        $grant = $membership->grants->getGrantById((int)$grantId);

        if (!$grant) {
            return $this->_sendErrorResponse(Craft::t('membership', 'The grant does not exist.'));
        }

        if (!$membership->grants->deleteGrant($grant)) {
            return $this->_sendErrorResponse(Craft::t('membership', 'The grant could not be deleted.'));
        }

        return $this->_sendSuccessResponse(Craft::t('membership', 'Grant deleted.'));
    }
}
