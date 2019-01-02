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
use craft\web\assets\cp\CpAsset;

/**
 * @author    Matt Gray
 * @package   ListBuilder
 * @since     1.0.0
 */
class ListBuilderAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = "@mattgrayisok/listbuilder/assetbundles/listbuilder/dist";

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/Index.js',
            'js/datatables.min.js',
            'js/chartjs-2.7.3.js',
        ];

        $this->css = [
            'css/Index.css',
            'css/datatables.min.css',
        ];

        parent::init();
    }
}
