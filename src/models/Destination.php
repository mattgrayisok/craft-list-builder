<?php
/**
 * List Builder plugin for Craft CMS 3.x
 *
 * Automatically generate social media posts when you perform actions within the Craft control panel.
 *
 * @link      https://mattgrayisok.com
 * @copyright Copyright (c) 2018 Matt Gray
 */

namespace mattgrayisok\listbuilder\models;

use mattgrayisok\listbuilder\ListBuilder;
use mattgrayisok\listbuilder\Enums;

use Craft;
use craft\base\Model;
use craft\db\Query;
use yii\behaviors\AttributeTypecastBehavior;

/**
 * @author    Matt Gray
 * @package   ListBuilder
 * @since     1.0.0
 */
class Destination extends Model
{
    /**
     * @var integer
     */
    public $id = 0;
    public $dateEnabled = null;
    public $type = Enums::DESTINATION_TYPE__WEBHOOK;
    public $config = '{}';
    public $name = '';
    public $errorMsg = null;
    public $enabled = true;

    public $boolCasts = [
        'disableonerror',
    ];

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['type', 'integer'],
            ['config', 'string'],
            ['name', 'string'],
            ['errorMsg', 'string'],
            ['enabled', 'boolean'],
        ];
    }

    public function getTypeName()
    {
        return Enums::destinationTypeToString($this->type);
    }

    public function getConfigValue($name)
    {
        $decoded = json_decode($this->config, true);
        if(array_key_exists($name, $decoded)){
            return $decoded[$name];
        }
        return null;
    }

    public function getUrlForTypeIcon()
    {
        return Craft::$app->view->getAssetManager()->getPublishedUrl('@mattgrayisok/listbuilder/assetbundles/listbuilder/dist', true) . '/img/destinationType' . $this->type . '.svg';
    }

    public function getFailedSubmissionsCount()
    {
        return ListBuilder::$plugin->destinationManager->getFailedSubmissionsCount($this);
    }

}
