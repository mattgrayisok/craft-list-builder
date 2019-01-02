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
class Signup extends Model
{
    /**
     * @var integer
     */
    public $id = 0;
    public $email = '';
    public $sourceId = null;
    public $dateCreated = null;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['email', 'integer'],
            ['sourceId', 'integer'],
        ];
    }
}
