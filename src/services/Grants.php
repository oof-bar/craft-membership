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

use craft\commerce\base\Plan;

use oofbar\membership\models\Grant;
use oofbar\membership\records\Grant as GrantRecord;

/**
 * Grants Service
 *
 * Methods related to saving and fetching Grants.
 *
 * @author    oof. Studio
 * @package   Membership
 * @since     1.0.0
 */
class Grants extends Component
{
    /**
     * Get Grants matching the passed criteria.
     * 
     * @param array $criteria Criteria to apply to the Query.
     * @return Grant[]
     */
    public function getGrants(array $criteria = [])
    {
        $rows = $this->_createGrantQuery()
            ->where($criteria)
            ->all();

        return $this->_populateGrantModels($rows);
    }

    /**
     * Get an existing Grant model by ID.
     * 
     * @param int $id
     * @return Grant|null
     */
    public function getGrantById(int $id)
    {
        $row = $this->_createGrantQuery()
            ->where(['id' => $id])
            ->one();

        if (!$row) {
            return null;
        }

        return new Grant($row);
    }

    /**
     * Gets grants for the passed Plan.
     * 
     * @param Plan $plan
     * @return Grant[]
     */
    public function getGrantsForPlan(Plan $plan): array
    {
        $rows = $this->_createGrantQuery()
            ->where(['planId' => $plan->id])
            ->all();

        return $this->_populateGrantModels($rows);
    }

    /**
     * Gets all grants.
     * 
     * @return Grant[]
     */
    public function getAllGrants(): array
    {
        $rows = $this->_createGrantQuery()
            ->all();

        return $this->_populateGrantModels($rows);
    }

    /**
     * Saves a grant.
     * 
     * @param Grant $grant
     * @return bool
     */
    public function saveGrant(Grant $grant): bool
    {
        $isNew = !$grant->id;

        if (!$grant->validate()) {
            return false;
        }

        if (!$isNew) {
            $record = GrantRecord::findOne($grant->id);
        } else {
            $record = new GrantRecord;
        }

        $record->name = $grant->name;
        $record->enabled = $grant->enabled;
        $record->planId = $grant->planId;
        $record->userGroupId = $grant->userGroupId;

        if (!$record->save()) {
            return false;
        }

        // Set any new properties back onto the model:
        $grant->id = $record->id;
        $grant->dateCreated = $record->dateCreated;
        $grant->dateUpdated = $record->dateUpdated;
        $grant->uid = $record->uid;

        return true;
    }

    /**
     * Deletes the passed Grant.
     * 
     * @param Grant $grant
     * @return bool Whether or not the deletion was successful.
     */
    public function deleteGrant(Grant $grant): bool
    {
        $grantRecord = GrantRecord::findOne($grant->id);

        return $grantRecord->delete();
    }

    /**
     * Prepares a Grants query
     * 
     * @return Query
     */
    private function _createGrantQuery(): Query
    {
        return (new Query)
            ->select([
                'id',
                'name',
                'enabled',
                'planId',
                'userGroupId',
                'dateCreated',
                'dateUpdated',
                'uid'
            ])
            ->from('{{%membership_grants}}')
            ->orderBy('dateCreated ASC');
    }

    /**
     * Populates Grant models from an array (i.e. from the database)
     * 
     * @param array $rows
     * @return Grant[]
     */
    private function _populateGrantModels(array $rows): array
    {
        return array_map(function ($row) {
            return new Grant($row);
        }, $rows);
    }
}