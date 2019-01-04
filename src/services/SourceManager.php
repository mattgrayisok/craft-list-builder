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
use mattgrayisok\listbuilder\models\Source;
use mattgrayisok\listbuilder\records\Source as SourceRecord;

use Craft;
use craft\base\Component;

/**
 * @author    Matt Gray
 * @package   ListBuilder
 * @since     1.0.0
 */
class SourceManager extends Component
{
    // Public Methods
    // =========================================================================

    public function getAllSources()
    {
        $myResults = SourceRecord::find()->all();
        return $this->_recordsToModels($myResults);
    }

    public function getSourceCount()
    {
        return SourceRecord::find()->count();
    }

    public function getAllPopups()
    {
        $myResults = SourceRecord::find()
            ->where(['type' => 3])
            ->all();
        return $this->_recordsToModels($myResults);
    }

    public function getSourceById($id)
    {
        $myResult = SourceRecord::find()->where(['id' => $id])->one();

        if (is_null($myResult)) {
            return null;
        }

        return $this->_recordToModel($myResult);
    }

    public function getSourceByShortcode($shortcode)
    {
        $myResult = SourceRecord::find()->where(['shortcode' => $shortcode])->one();

        if (is_null($myResult)) {
            return null;
        }

        return $this->_recordToModel($myResult);
    }

    /*
     * @return mixed
     */
    public function saveSource(Source $model, bool $runValidation = true)
    {
        $isNewModel = !$model->id;

        if ($runValidation && !$model->validate()) {
            Craft::info('Source not saved due to validation error.', __METHOD__);
            return false;
        }

        $duplicate = SourceRecord::find()
            ->where(['shortcode' => $model->shortcode])
            ->all();

        if (sizeof($duplicate) > 0) {
            Craft::info('Source not saved due to validation error.', __METHOD__);
            $model->addError('shortcode', 'A source with this code already exist');
            return false;
        }

        if ($isNewModel) {
            $record = new SourceRecord();
        } else {
            $record = SourceRecord::findOne($model->id);
            if (!$record) {
                throw new \Exception('No rule exists with the ID “{id}”');
            }
        }

        $record->type       = $model->type;
        $record->config     = $model->config;
        $record->shortcode  = $model->shortcode;
        $record->name       = $model->name;

        $record->save(false);

        if (!$model->id) {
            $model->id = $record->id;
        }

        return $model;
    }

    public function deleteSourceById($id)
    {
        return Craft::$app->getDb()->createCommand()
            ->delete('{{%listbuilder_source}}', ['id' => $id])
            ->execute();
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
        return new Source($record->toArray([
            'id',
            'type',
            'config',
            'shortcode',
            'name',
        ]));
    }
}
