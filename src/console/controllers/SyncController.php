<?php
/**
 * List Builder plugin for Craft CMS 3.x
 *
 * Automatically generate social media posts when you perform actions within the Craft control panel.
 *
 * @link      https://mattgrayisok.com
 * @copyright Copyright (c) 2018 Matt Gray
 */

namespace mattgrayisok\listbuilder\console\controllers;

use mattgrayisok\listbuilder\ListBuilder;

use Craft;
use craft\helpers\App;

use yii\console\Controller;

/**
 * ListBuilder sync command
 *
 * @author    Matt Gray
 * @package   ListBuilder
 * @since     1.0.0
 */
class SyncController extends Controller
{

    /**
     * Sync pending subscriptions to destinations
     */
    public function actionSync()
    {
        echo 'Syncing subscriptions'.PHP_EOL;

        App::maxPowerCaptain();
        ListBuilder::$plugin->taskManager->scheduleSync();
        Craft::$app->getQueue()->run();

    }

}
