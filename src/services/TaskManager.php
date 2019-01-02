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

use mattgrayisok\listbuilder\jobs\Sync;

use Craft;
use craft\base\Component;
use craft\db\Query;
use craft\web\View;
use yii\base\Exception;
use craft\helpers\Template;

/**
 * @author    Matt Gray
 * @package   ListBuilder
 * @since     1.0.0
 */
class TaskManager extends Component
{
    // Public Methods
    // =========================================================================

    private $queueDescription = 'Syncing subscriptions';

    public function taskExistsInQueue(){
        return (new Query())
            ->select(['id'])
            ->from(['{{%queue}}'])
            ->where(['description' => $this->queueDescription])
            ->count() > 0;
    }

    public function scheduleSync(){
        if (!$this->taskExistsInQueue()) {
            Craft::$app->queue->push(new Sync([
                'description' => $this->queueDescription
            ]));
        }
    }
}
