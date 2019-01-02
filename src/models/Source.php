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

/**
 * @author    Matt Gray
 * @package   ListBuilder
 * @since     1.0.0
 */
class Source extends Model
{
    /**
     * @var integer
     */
    public $id = 0;
    public $type = Enums::SOURCE_TYPE__CUSTOM;
    public $config = '{}';
    public $shortcode = '';
    public $name = '';
    public $boolCasts = [
        'ajax',
        'labels',
        'gdpr',
    ];

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['type', 'integer'],
            ['config', 'string'],
            ['shortcode', 'string'],
            ['name', 'string'],
        ];
    }

    public function getTypeName()
    {
        return Enums::sourceTypeToString($this->type);
    }

    public function getTotalEmailsCollected()
    {
        return ListBuilder::$plugin->signupManager->getSignupsFromSource($this);
    }

    public function getConfigValue($name)
    {

        $decoded = json_decode($this->config, true);
        if(!array_key_exists($name, $decoded)){
            return null;
        }
        return $decoded[$name];
    }

    public function getManualEmbedCode()
    {
        return '<form method="POST">
            <input type="hidden" name="action" value="list-builder/signup/store">
            <input type="hidden" name="source" value="'.$this->shortcode.'">
            {{ csrfInput() }}
            <input type="email" name="email" placeholder="email">
            <button type="submit">Subscribe</button>
        </form>';
    }
}
