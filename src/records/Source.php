<?php
/**
 * List Builder plugin for Craft CMS 3.x
 *
 * Email mailing list popup notification exit
 *
 * @link      https://mattgrayisok.com
 * @copyright Copyright (c) 2018 Matt Gray
 */

namespace mattgrayisok\listbuilder\records;

use mattgrayisok\listbuilder\ListBuilder;

use Craft;
use craft\db\ActiveRecord;

/**
 * @author    Matt Gray
 * @package   ListBuilder
 * @since     1.0.0
 */
class Source extends ActiveRecord
{
    // Public Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%listbuilder_source}}';
    }
}
