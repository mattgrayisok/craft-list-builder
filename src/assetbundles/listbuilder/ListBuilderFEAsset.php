<?php
/**
 * List Builder plugin for Craft CMS 3.x
 *
 * Email mailing list popup notification exit
 *
 * @link      https://mattgrayisok.com
 * @copyright Copyright (c) 2018 Matt Gray
 */

namespace mattgrayisok\listbuilder\assetbundles\listbuilder;

use Craft;
use craft\web\AssetBundle;

/**
 * @author    Matt Gray
 * @package   ListBuilder
 * @since     1.0.0
 */
class ListBuilderFEAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = "@mattgrayisok/listbuilder/assetbundles/listbuilder/dist-fe";

        $this->js = [
            'js/listbuilder.js',
        ];

        $this->css = [
            'css/listbuilder.css',
        ];

        parent::init();
    }
}
