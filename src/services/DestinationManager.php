<?php
/**
 * List Builder plugin for Craft CMS 3.x
 *
 * Email mailing list popup notification exit
 *
 * @link      https://mattgrayisok.com
 * @copyright Copyright (c) 2018 Matt Gray
 */

namespace mattgrayisok\listbuilder\services;

use mattgrayisok\listbuilder\ListBuilder;
use mattgrayisok\listbuilder\models\Destination;
use mattgrayisok\listbuilder\records\Destination as DestinationRecord;

use Craft;
use craft\base\Component;
use craft\db\Query;

/**
 * @author    Matt Gray
 * @package   ListBuilder
 * @since     1.0.0
 */
class DestinationManager extends Component
{
    // Public Methods
    // =========================================================================

    public function getAllDestinations()
    {
        $myResults = DestinationRecord::find()->all();
        return $this->_recordsToModels($myResults);
    }

    public function getDestinationCount()
    {
        return DestinationRecord::find()->count();
    }

    public function getEnabledDestinations()
    {
        $myResults = DestinationRecord::find()
            ->where(['enabled' => 1])
            ->all();

        return $this->_recordsToModels($myResults);
    }

    public function getDestinationById($id)
    {
        $myResult = DestinationRecord::find()->where(['id' => $id])->one();

        if (is_null($myResult)) {
            return null;
        }

        return $this->_recordToModel($myResult);
    }

    /*
     * @return mixed
     */
    public function saveDestination(Destination $model, bool $runValidation = true)
    {
        $isNewModel = !$model->id;

        if ($runValidation && !$model->validate()) {
            Craft::info('Destination not saved due to validation error.', __METHOD__);
            return false;
        }

        if ($isNewModel) {
            $record = new DestinationRecord();
        } else {
            $record = DestinationRecord::findOne($model->id);
            if (!$record) {
                throw new \Exception('No rule exists with the ID “{id}”');
            }
        }

        $record->dateEnabled  = $model->dateEnabled;
        $record->type         = $model->type;
        $record->config       = $model->config;
        $record->name         = $model->name;
        $record->errorMsg     = $model->errorMsg;
        $record->enabled      = $model->enabled;

        $record->save(false);

        if (!$model->id) {
            $model->id = $record->id;
        }

        return $model;
    }

    public function deleteDestinationById($id)
    {
        return Craft::$app->getDb()->createCommand()
            ->delete('{{%listbuilder_destination}}', ['id' => $id])
            ->execute();
    }

    public function getFailedSubmissionsCount($destination)
    {
        return (new Query())
            ->select(['id'])
            ->from(['{{%listbuilder_signup_destination}}'])
            ->where(['destinationId' => $destination->id])
            ->andWhere(['success' => false])
            ->count();
    }

    private function _recordsToModels($records)
    {
        foreach ($records as $key => $value) {
            $records[$key] = $this->_recordToModel($value);
        }
        return $records;
    }

    private function _recordToModel($record)
    {
        return new Destination($record->toArray([
            'id',
            'dateEnabled',
            'type',
            'config',
            'name',
            'errorMsg',
            'enabled',
        ]));
    }
}
