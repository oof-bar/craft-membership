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

use craft\commerce\records\Subscription as SubscriptionRecord;

use oofbar\membership\records\Grant as GrantRecord;

/**
 * Message Model
 *
 * A single audit/log message.
 *
 * @author    oof. Studio
 * @package   Membership
 * @since     1.0.0
 */
class Message extends Model
{
    /**
     * @var int ID
     */
    public $id;

    /**
     * @var string Message body
     */
    public $message;

    /**
     * @var string Grant ID
     */
    public $grantId;

    /**
     * @var string Subscription ID
     */
    public $subscriptionId;

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
            [['message'], 'required'],

            [['grantId'], 'required'],
            [
                ['grantId'],
                'exist',
                'targetClass' => GrantRecord::class,
                'targetAttribute' => ['grantId' => 'id']
            ],

            [['subscriptionId'], 'required'],
            [
                ['subscriptionId'],
                'exist',
                'targetClass' => SubscriptionRecord::class,
                'targetAttribute' => ['subscriptionId' => 'id']
            ],
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
}