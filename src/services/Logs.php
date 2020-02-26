<?php
/**
 * Membership plugin for Craft CMS 3.x
 *
 * Give your users special access based on their Commerce Subscriptions.
 *
 * @link      https://oof.studio/
 * @copyright Copyright (c) 2020 oof. Studio
 */

namespace oofbar\membership\services;

use Craft;
use craft\base\Component;
use craft\db\Query;

use oofbar\membership\models\Message;
use oofbar\membership\records\Message as MessageRecord;

/**
 * Logs Service
 *
 * Handles functionality related to the built-in logs / auditing system.
 *
 * @author    oof. Studio
 * @package   Membership
 * @since     1.0.0
 */
class Logs extends Component
{
    /**
     * Inserts a Message into the log.
     * 
     * @param Message $message
     * @return bool Whether or not it was logged.
     */
    public function log(Message $message): bool
    {
        if (!$message->validate()) {
            return false;
        }

        $record = new MessageRecord([
            'message' => $message->message,
            'grantId' => $message->grantId,
            'subscriptionId' => $message->subscriptionId,
        ]);

        return $record->save();
    }

    /**
     * Gets a single message by ID
     * 
     * @param int ID
     * @return Message|null
     */
    public function getMessageById(int $id)
    {
        $row = $this->_createMessagesQuery()
            ->where(['id' => $id])
            ->one();

        if (!$row) {
            return null;
        }

        return new Message($row);
    }

    /**
     * Builds a query ready to fetch log messages.
     * 
     * @param array $criteria A valid array used to prime the WHERE portion of the query.
     * @return Message[]
     */
    public function getMessages(array $criteria = []): array
    {
        $rows = $this->_createMessagesQuery()
            ->where($criteria)
            ->all();

        return array_map(function ($row) {
            return new Message($row);
        }, $rows);
    }

    /**
     * Prepares a Messages query
     * 
     * @return Query
     */
    private function _createMessagesQuery(): Query
    {
        return (new Query)
            ->select([
                'id',
                'message',
                'grantId',
                'subscriptionId',
                'dateCreated',
                'dateUpdated',
                'uid'
            ])
            ->from('{{%membership_logs}}')
            ->orderBy('id DESC');
    }
}