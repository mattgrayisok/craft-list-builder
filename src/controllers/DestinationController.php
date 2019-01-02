<?php
/**
 * List Builder plugin for Craft CMS 3.x
 *
 * Automatically generate social media posts when you perform actions within the Craft control panel.
 *
 * @link      https://mattgrayisok.com
 * @copyright Copyright (c) 2018 Matt Gray
 */

namespace mattgrayisok\listbuilder\controllers;

use mattgrayisok\listbuilder\ListBuilder;
use mattgrayisok\listbuilder\Enums;
use mattgrayisok\listbuilder\models\Destination;
use mattgrayisok\listbuilder\jobs\Sync;

use Craft;
use craft\web\Controller;

/**
 * @author    Matt Gray
 * @package   ListBuilder
 * @since     1.0.0
 */
class DestinationController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = [];

    // Public Methods
    // =========================================================================

    /**
     * @return mixed
     */
    public function actionIndex()
    {
        $variables = [];
        $variables['destinations'] = ListBuilder::$plugin->destinationManager->getAllDestinations();
        return $this->renderTemplate('list-builder/destinations/index', $variables);
    }

    public function actionShowEdit($destinationId = null)
    {
        $variables = [];
        if ($destinationId) {
            $variables['destination'] = ListBuilder::$plugin->destinationManager->getDestinationById($destinationId);
        } else {
            $variables['destination'] = new Destination();
        }

        return $this->renderTemplate('list-builder/destinations/edit', $variables);
    }

    public function actionEdit()
    {
        $this->requirePostRequest();
        $request = Craft::$app->getRequest();

        $destination = $this->_getModelFromPost();
        return $this->_saveAndRedirect($destination, 'list-builder/destinations/', true);
    }

    public function actionEnable()
    {
        $this->requirePostRequest();
        $request = Craft::$app->getRequest();
        $destination = ListBuilder::$plugin->destinationManager->getDestinationById($request->getBodyParam('destinationId'));

        if(is_null($destination)){
            return null;
        }

        $destination->enabled = true;
        //If we're recovering from an error, don't change the last enabled date
        //This will cause us to back-fill any emails we missed during downtime

        //If we are not recovering from an error then the downtime was manual and
        //therefore we shouldn't backfill so we set the enabled date to now
        if(is_null($destination->errorMsg)){
            $destination->dateEnabled = (new \DateTime())->format('Y-m-d H:i:s');
        }
        $destination->errorMsg = null;
        return $this->_saveAndRedirect($destination, 'list-builder/destinations/', true);
    }

    public function actionDisable()
    {
        $this->requirePostRequest();
        $request = Craft::$app->getRequest();
        $destination = ListBuilder::$plugin->destinationManager->getDestinationById($request->getBodyParam('destinationId'));

        if(is_null($destination)){
            return null;
        }

        $destination->enabled = false;
        return $this->_saveAndRedirect($destination, 'list-builder/destinations/', true);
    }

    public function actionDelete()
    {
        $this->requirePostRequest();
        $request = Craft::$app->getRequest();
        $destinationId = $request->getParam('destinationId');

        $result = ListBuilder::$plugin->destinationManager->deleteDestinationById($destinationId);
    }

    public function actionSync()
    {
        Craft::$app->queue->push(new Sync([
            'description' => 'Syncing signups'
        ]));
    }

    public function actionSyncDash()
    {
        Craft::$app->queue->push(new Sync([
            'description' => 'Syncing signups'
        ]));
        return $this->redirect('list-builder/destinations/');
    }

    private function _saveAndRedirect($destination, $redirect)
    {
        if (!ListBuilder::$plugin->destinationManager->saveDestination($destination)) {
            Craft::$app->getSession()->setError(Craft::t('list-builder', 'Unable to save destination.'));
            Craft::$app->getUrlManager()->setRouteParams([
                'destination' => $destination,
            ]);
            return null;
        }
        Craft::$app->getSession()->setNotice(Craft::t('list-builder', 'Destination saved.'));
        return $this->redirect($redirect);
    }

    private function _getModelFromPost()
    {
        $this->requirePostRequest();
        $request = Craft::$app->getRequest();
        if ($request->getBodyParam('destinationId')) {
            $destination = ListBuilder::$plugin->destinationManager->getDestinationById($request->getBodyParam('destinationId'));
        } else {
            $destination = new Destination();
            $destination->dateEnabled = (new \DateTime())->format('Y-m-d H:i:s');
        }

        //TODO: Need to validate config is ok for the remote type

        $config = [];
        $allParams = $request->getBodyParams();
        foreach($allParams as $key => $value){
            if (substr($key, 0, 5) == 'conf-') {
                $confName = substr($key, 5);
                $config[$confName] = $value;
                if(in_array($confName, $destination->boolCasts)){
                    $config[$confName] = $value == '' ? false : (bool)$value;
                }
            }
        }

        $destination->type = $request->getBodyParam('type', $destination->type);
        $destination->name = empty($request->getBodyParam('name')) ?
                            'New ' . Enums::destinationTypeToString($destination->type) :
                            $request->getBodyParam('name');
        $destination->config = json_encode($config);
        $destination->errorMsg = $destination->errorMsg;
        $destination->enabled = $request->getBodyParam('enabled', $destination->enabled);

        return $destination;

    }

}
