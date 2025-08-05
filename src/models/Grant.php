<?php
/**
 * Membership plugin for Craft CMS 4.x
 *
 * Give your users special access based on their Commerce Subscriptions.
 *
 * @link      https://oof.studio/
 * @copyright Copyright (c) 2023 oof. Studio
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

use DateTime;

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
     * @var int|null ID
     */
    public ?int $id = null;

    /**
     * @var string Name or reference label for the Grant
     */
    public string $name = '';

    /**
     * @var bool Enabled
     */
    public bool $enabled = true;

    /**
     * @var int|null Plan ID
     */
    public ?int $planId = null;

    /**
     * @var int|null UserGroup ID
     */
    public ?int $userGroupId = null;

    /**
     * @var DateTime|null
     */
    public ?DateTime $dateCreated = null;

    /**
     * @var DateTime|null
     */
    public ?DateTime $dateUpdated = null;

    /**
     * @var string|null UID
     */
    public ?string $uid = null;

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
                'message' => Craft::t('membership', 'You must select a valid plan.'),
            ],

            [['userGroupId'], 'required'],
            [
                ['userGroupId'],
                'exist',
                'targetClass' => UserGroupRecord::class,
                'targetAttribute' => ['userGroupId' => 'id'],
                'message' => Craft::t('membership', 'You must select a valid user group.'),
            ],

            // Validate uniqueness of effect among other Grants:
            [
                ['planId', 'userGroupId'],
                'unique',
                'targetClass' => GrantRecord::class,
                'targetAttribute' => ['planId', 'userGroupId'],
                'filter' => ['not', ['id' => $this->id]],
                'message' => Craft::t('membership', 'A grant with this combination of settings already exists.'),
            ],
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
