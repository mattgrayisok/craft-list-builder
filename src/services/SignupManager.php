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
use mattgrayisok\listbuilder\models\Signup;
use mattgrayisok\listbuilder\models\Source;
use mattgrayisok\listbuilder\records\Signup as SignupRecord;

use Craft;
use craft\base\Component;
use craft\db\Query;


/**
 * @author    Matt Gray
 * @package   ListBuilder
 * @since     1.0.0
 */
class SignupManager extends Component
{
    // Public Methods
    // =========================================================================

    /*
     * @return mixed
     */
    public function storeSignup(Signup $signup)
    {
        $duplicate = SignupRecord::find()
            ->where(['email' => $signup->email])
            ->all();

        if (sizeof($duplicate) > 0) {
            return true;
        }

        $record = new SignupRecord();
        $record->email = $signup->email;
        $record->sourceId = $signup->sourceId;
        $record->consent = $signup->consent;
        $record->save();

        return true;
    }

    public function getSignupsFromSource(Source $source)
    {
        return SignupRecord::find()
            ->where(['sourceId' => $source->id])
            ->count();
    }

    public function getPendingSignupsForDestination($destination, $limit = 1000)
    {
        $subQuery = (new Query())
            ->select(['signupId'])
            ->from(['{{%listbuilder_signup_destination}}'])
            ->where(['destinationId' => $destination->id]);
        $myResults = SignupRecord::find()
            ->where(['not in', 'id', $subQuery])
            ->andWhere(['>=', 'dateCreated', $destination->dateEnabled])
            ->limit($limit)
            ->all();

        return $this->_recordsToModels($myResults);
    }

    public function getFailedSignupsForDestination($destination, $limit = 1000)
    {
        $subQuery = (new Query())
            ->select(['signupId'])
            ->from(['{{%listbuilder_signup_destination}}'])
            ->where(['destinationId' => $destination->id])
            ->andWhere(['success' => false]);
        $myResults = SignupRecord::find()
            ->where(['in', 'id', $subQuery])
            ->limit($limit)
            ->all();

        return $this->_recordsToModels($myResults);
    }

    public function datatablesData($start, $length, $search, $order)
    {
        $total = SignupRecord::find()
            ->count();

        $searchVal = $search['value'];

        $filtered = SignupRecord::find();
        if(!empty($searchVal)){
            $filtered = $filtered->where(['like', 'email', $searchVal.'%', false]);
        }

        $filteredTotal = $filtered->count();

        $orderCol = 'email';
        $orderDir = SORT_ASC;
        if ($order[0]['column'] == 1) $orderCol = 'dateCreated';
        if ($order[0]['dir'] == 'desc') $orderDir = SORT_DESC;
        $filtered = $filtered->orderBy([$orderCol => $orderDir]);

        $filteredRecords = $filtered
            ->limit($length)
            ->offset($start)
            ->all();

        $models = $this->_recordsToModels($filteredRecords);

        return [
            'models' => $models,
            'total' => $total,
            'filteredTotal' => $filteredTotal
        ];

    }

    public function graphData()
    {
        $days = 7;
        $currentDay = new \DateTime();

        $labels = [];
        $data = [];
        for($i = 0; $i < $days; $i++){
            $labels[] = $currentDay->format('d/m/Y');
            $total = SignupRecord::find()
                ->where(['between', 'dateCreated',
                    $currentDay->format("Y-m-d").' 00:00:00',
                    $currentDay->format("Y-m-d").' 23:59:59'
                ])
                ->count();
            $data[] = $total;
            $currentDay->sub(new \DateInterval('P1D'));
        }

        return [
            'labels' => array_reverse($labels),
            'data' => array_reverse($data)
        ];
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
        return new Signup($record->toArray([
            'id',
            'dateCreated',
            'email',
            'sourceId',
            'consent',
        ]));
    }
}
