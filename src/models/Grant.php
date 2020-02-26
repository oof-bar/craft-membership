<?php
/**
 * Membership plugin for Craft CMS 3.x
 *
 * Give your users special access based on their Commerce Subscriptions.
 *
 * @link      https://oof.studio/
 * @copyright Copyright (c) 2020 oof. Studio
 */

namespace oofbar\membership\models;

use Craft;
use craft\base\Model;
use craft\models\UserGroup;
use craft\records\UserGroup as UserGroupRecord;

use craft\commerce\Plugin as Commerce;
use craft\commerce\base\Plan;
use craft\commerce\records\Plan as PlanRecord;

use oofbar\membership\records\Grant as GrantRecord;

/**
 * Grant Model
 *
 * Represents a single permission grant, triggered by a Subscription lifecycle event.
 *
 * @author    oof. Studio
 * @package   Membership
 * @since     1.0.0
 */
class Grant extends Model
{
    /**
     * @var int ID
     */
    public $id;

    /**
     * @var string Name or reference label for the Grant
     */
    public $name;

    /**
     * @var bool Enabled
     */
    public $enabled;

    /**
     * @var int Plan ID
     */
    public $planId;

    /**
     * @var int UserGroup ID
     */
    public $userGroupId;

    /**
     * @var \DateTime
     */
    public $dateCreated;

    /**
     * @var \DateTime
     */
    public $dateUpdated;

    /**
     * @var string UID
     */
    public $uid;

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 255],

            [['planId'], 'required'],
            [
                ['planId'],
                'exist',
                'targetClass' => PlanRecord::class,
                'targetAttribute' => ['planId' => 'id'],
                'message' => 'You must select a valid Plan.'
            ],

            [['userGroupId'], 'required'],
            [
                ['userGroupId'],
                'exist',
                'targetClass' => UserGroupRecord::class,
                'targetAttribute' => ['userGroupId' => 'id'],
                'message' => 'You must select a valid User Group.'
            ],

            // Validate uniqueness of effect among other Grants:
            [
                ['planId', 'userGroupId'],
                'unique',
                'targetClass' => GrantRecord::class,
                'targetAttribute' => ['planId', 'userGroupId'],
                'filter' => ['!=', 'id', $this->id],
                'message' => 'A grant with this combination of settings already exists.'
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function dateTimeAttributes(): array
    {
        return [
            'dateCreated',
            'dateUpdated',
        ];
    }

    /**
     * Gets the Plan associated with the Grant.
     * 
     * @return Plan
     */
    public function getPlan(): Plan
    {
        return Commerce::getInstance()->getPlans()->getPlanById($this->planId);
    }

    /**
     * Gets the UserGroup associated with the Grant.
     * 
     * @return UserGroup
     */
    public function getUserGroup(): UserGroup
    {
        return Craft::$app->getUserGroups()->getGroupById($this->userGroupId);
    }
}