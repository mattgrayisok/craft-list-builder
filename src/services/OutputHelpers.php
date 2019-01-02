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
use craft\web\View;
use yii\base\Exception;
use craft\helpers\Template;

/**
 * @author    Matt Gray
 * @package   ListBuilder
 * @since     1.0.0
 */
class OutputHelpers extends Component
{
    // Public Methods
    // =========================================================================

    public function inline($code)
    {
        $html = $this->_wrapModeChange(function() use ($code){
            $source = ListBuilder::$plugin->sourceManager->getSourceByShortcode($code);
            if(is_null($source)){
                return '';
            }
            $variables = [];
            $variables['rand'] = mt_rand(0, 1000);
            $variables['source'] = $source;
            return Craft::$app->getView()->renderTemplate('list-builder/frontend/inline/output', $variables);
        });

        return Template::raw($html);
    }

    private function _wrapModeChange($callback)
    {
        $oldMode = Craft::$app->view->getTemplateMode();
        try {
            Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_CP);
        } catch (Exception $e) {
            Craft::error($e->getMessage(), __METHOD__);
        }

        $out = $callback();

        try {
            Craft::$app->view->setTemplateMode($oldMode);
        } catch (Exception $e) {
            Craft::error($e->getMessage(), __METHOD__);
        }

        return $out;
    }

    public function renderPopupCode()
    {
        $html = $this->_wrapModeChange(function(){

            $popups = ListBuilder::$plugin->sourceManager->getAllPopups();

            $output = '';

            foreach($popups as $popup){
                $variables = [];
                $variables['rand'] = mt_rand(0, 1000);
                $variables['source'] = $popup;
                $output .= Craft::$app->getView()->renderTemplate('list-builder/frontend/popup/output', $variables);
            }

            return $output;
        });

        return Template::raw($html);

    }
}
