<?php
/**
 * Membership plugin for Craft CMS 4.x
 *
 * Give your users special access based on their Commerce Subscriptions.
 *
 * @link      https://oof.studio/
 * @copyright Copyright (c) 2023 oof. Studio
 */

namespace oofbar\membership\records;

use Craft;
use craft\db\ActiveRecord;

/**
 * Message Record
 *
 * @author    oof. Studio
 * @package   Membership
 * @since     1.0.0
 */
class Message extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return '{{%membership_logs}}';
    }
}
