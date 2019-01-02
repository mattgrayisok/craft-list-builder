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

use mattgrayisok\listbuilder\models\Signup;

use Craft;
use craft\web\Controller;

/**
 * @author    Matt Gray
 * @package   ListBuilder
 * @since     1.0.0
 */
class DashboardController extends Controller
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
        $variables['sourceCount'] = ListBuilder::$plugin->sourceManager->getSourceCount();
        $variables['destinationCount'] = ListBuilder::$plugin->destinationManager->getDestinationCount();
        return $this->renderTemplate('list-builder/dashboard/index', $variables);
    }

}
