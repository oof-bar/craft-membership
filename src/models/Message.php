<?php
/**
 * Membership plugin for Craft CMS 5.x
 *
 * Give your users special access based on their Commerce Subscriptions.
 *
 * @link      https://oof.studio/
 * @copyright Copyright (c) 2025 oof. Studio
 */

namespace oofbar\membership\models;

use Craft;
use craft\base\Model;

use craft\commerce\records\Subscription as SubscriptionRecord;

use oofbar\membership\records\Grant as GrantRecord;

use DateTime;

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
     * @var int|null ID
     */
    public ?int $id = null;

    /**
     * @var string Message body
     */
    public string $message = '';

    /**
     * @var int|null Grant ID
     */
    public ?int $grantId = null;

    /**
     * @var int|null Subscription ID
     */
    public ?int $subscriptionId = null;

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
}
