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

use mattgrayisok\listbuilder\models\Signup;
use mattgrayisok\listbuilder\models\Source;

use Craft;
use craft\web\Controller;
use craft\elements\Asset;

/**
 * @author    Matt Gray
 * @package   ListBuilder
 * @since     1.0.0
 */
class SourceController extends Controller
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
        $variables['sources'] = ListBuilder::$plugin->sourceManager->getAllSources();
        return $this->renderTemplate('list-builder/sources/index', $variables);
    }

    public function actionInstructions($sourceId)
    {
        $variables = [];
        $source = ListBuilder::$plugin->sourceManager->getSourceById($sourceId);
        $variables['source'] = $source;
        return $this->renderTemplate('list-builder/sources/instructions/type'.$source->type, $variables);
    }

    public function actionShowEdit($sourceId = null, $source = null)
    {
        $variables = [];
        $variables['elementType'] = Asset::class;
        $variables['headerimageElements'] = [];
        if(!$source){
            if ($sourceId) {
                $source = ListBuilder::$plugin->sourceManager->getSourceById($sourceId);
                $variables['source'] = $source;
                if(!is_null($source->getConfigValue('headerimage'))){
                    $variables['headerimageElements'] = $this->_imageAssetsFromIds($source->getConfigValue('headerimage'));
                }
            } else {
                $variables['source'] = new Source();
            }
        }else{
            $variables['source'] = $source;
        }

        return $this->renderTemplate('list-builder/sources/edit', $variables);
    }

    public function actionEdit()
    {
        $this->requirePostRequest();
        $request = Craft::$app->getRequest();

        $source = $this->_getModelFromPost();
        $r =  $this->_saveAndRedirect($source, 'list-builder/sources/', true);
        return $r;
    }

    public function actionDelete()
    {
        $this->requirePostRequest();
        $request = Craft::$app->getRequest();
        $sourceId = $request->getParam('sourceId');

        $result = ListBuilder::$plugin->sourceManager->deleteSourceById($sourceId);
    }

    private function _saveAndRedirect($source, $redirect)
    {
        if (!ListBuilder::$plugin->sourceManager->saveSource($source)) {
            Craft::$app->getSession()->setError(Craft::t('list-builder', 'Unable to save source.'));
            Craft::$app->getUrlManager()->setRouteParams([
                'source' => $source,
            ]);
            return null;
        }
        Craft::$app->getSession()->setNotice(Craft::t('list-builder', 'Source saved.'));
        return $this->redirect($redirect);
    }

    private function _getModelFromPost()
    {
        $this->requirePostRequest();
        $request = Craft::$app->getRequest();
        if ($request->getBodyParam('sourceId')) {
            $source = ListBuilder::$plugin->sourceManager->getSourceById($request->getBodyParam('sourceId'));
        } else {
            $source = new Source();
        }

        //TODO: Need to validate config is ok for the remote type

        $config = [];
        $allParams = $request->getBodyParams();
        foreach($allParams as $key => $value){
            if (substr($key, 0, 5) == 'conf-') {
                $confName = substr($key, 5);
                $config[$confName] = $value;
                if(in_array($confName, $source->boolCasts)){
                    $config[$confName] = $value == '' ? false : (bool)$value;
                }
            }
        }

        $source->shortcode = $request->getBodyParam('shortcode', $source->shortcode);
        if(empty($source->shortcode)){
            $source->shortcode = strtoupper(substr(md5(microtime()),rand(0,26),5));
        }else{
            $source->shortcode = $request->getBodyParam('shortcode', $source->shortcode);
        }
        $source->type = $request->getBodyParam('type', $source->type);
        $source->config = json_encode($config);
        $source->name = empty($request->getBodyParam('name')) ?
                            'New ' . Enums::sourceTypeToString($source->type) :
                            $request->getBodyParam('name');

        return $source;

    }

    private function _imageAssetsFromIds($assetIds)
    {
        $elements = Craft::$app->getElements();
        $assets = [];
        if (!empty($assetIds)) {
            if (\is_array($assetIds)) {
                foreach ($assetIds as $assetId) {
                    $assets[] = $elements->getElementById(intval($assetId), Asset::class);
                }
            } else {
                $assetId = $assetIds;
                $assets[] = $elements->getElementById(intval($assetId), Asset::class);
            }
        }
        return $assets;
    }

}
