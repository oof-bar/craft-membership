<?php
/**
 * Membership plugin for Craft CMS 4.x
 *
 * Give your users special access based on their Commerce Subscriptions.
 *
 * @link      https://oof.studio/
 * @copyright Copyright (c) 2023 oof. Studio
 */

namespace oofbar\membership\controllers;

use Craft;
use craft\web\Controller;

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
class GrantsController extends Controller
{
    /**
     * @inheritdoc
     */
    public function beforeAction($action): bool
    {
        $this->requireAdmin(false);

        return parent::beforeAction($action);
    }

    /**
     * Displays a list of Grants to an administrator
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $grants = Membership::getInstance()->getGrants()->getAllGrants();

        // We’ll use this to determine whether any plans exist, and display a message...
        $plans = Commerce::getInstance()->getPlans()->getAllPlans();

        // ...same for user groups:
        $groups = Craft::$app->getUserGroups()->getAllGroups();

        return $this->renderTemplate('membership/_grants/index', [
            'grants' => $grants,
            'plans' => $plans,
            'groups' => $groups,
            'canCreateGrants' => !empty($plans) && !empty($groups),
        ]);
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
            $grant = Membership::getInstance()->getGrants()->getGrantById($grantId);

            if (!$grant) {
                throw new HttpException(404, Craft::t('membership', 'The grant does not exist.'));
            }
        } else {
            $grant = new Grant;
        }

        return $this->renderTemplate('membership/_grants/edit', compact('grant', 'plans', 'userGroups'));
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
            $grant = Membership::getInstance()->getGrants()->getGrantById($id);

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

        if (!Membership::getInstance()->getGrants()->saveGrant($grant)) {
            return $this->asModelFailure($grant, Craft::t('membership', 'Failed to save grant.'), 'grant');
        }

        return $this->asModelSuccess($grant, Craft::t('membership', 'Grant saved.'), 'grant');
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
            return $this->asFailure(Craft::t('membership', 'No ID was provided'));
        }

        $grant = $membership->getGrants()->getGrantById((int)$grantId);

        if (!$grant) {
            return $this->asFailure(Craft::t('membership', 'The grant does not exist.'));
        }

        if (!$membership->getGrants()->deleteGrant($grant)) {
            return $this->asModelFailure($grant, Craft::t('membership', 'The grant could not be deleted.'), 'grant');
        }

        return $this->asModelSuccess($grant, Craft::t('membership', 'Grant deleted.'));
    }
}
