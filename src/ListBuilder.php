<?php
/**
 * List Builder plugin for Craft CMS 3.x
 *
 * Email mailing list popup notification exit
 *
 * @link      https://mattgrayisok.com
 * @copyright Copyright (c) 2018 Matt Gray
 */

namespace mattgrayisok\listbuilder;

use mattgrayisok\listbuilder\services\SignupManager;
use mattgrayisok\listbuilder\services\SourceManager;
use mattgrayisok\listbuilder\services\DestinationManager;
use mattgrayisok\listbuilder\services\OutputHelpers;

use mattgrayisok\listbuilder\assetbundles\listbuilder\ListBuilderFEAsset;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\web\UrlManager;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterCpNavItemsEvent;
use craft\web\twig\variables\CraftVariable;
use craft\elements\Entry;
use craft\web\twig\variables\Cp;
use craft\web\View;

use yii\base\Event;

/**
 * Class ListBuilder
 *
 * @author    Matt Gray
 * @package   ListBuilder
 * @since     1.0.0
 *
 * @property  SignupManager $signupManager
 */
class ListBuilder extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var ListBuilder
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $schemaVersion = '1.0.0';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        $this->registerComponentsAndServices();
        $this->registerCPRoutes();
        $this->registerGlobalRoutes();
        $this->registerGlobalRoutes();

        //Once all plugins have loaded
        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_LOAD_PLUGINS,
            function () {
                // Install these only after all other plugins have loaded
                $request = Craft::$app->getRequest();
                // Only respond to non-console site requests
                if ($request->getIsSiteRequest() && !$request->getIsConsoleRequest()) {
                    $this->handleSiteRequest();
                }
                // Respond to Control Panel requests
                //if ($request->getIsCpRequest() && !$request->getIsConsoleRequest()) {
                //    $this->handleAdminCpRequest();
                //}
            }
        );
    }

    public function registerGlobalRoutes()
    {
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                //$event->rules['siteActionTrigger1'] = 'list-builder/default';
            }
        );
    }

    public function getCpNavItem()
    {
        $item = parent::getCpNavItem();
        $item['badgeCount'] = 0;
        $item['subnav'] = [
            'dashboard' => ['label' => 'Dashboard', 'url' => 'list-builder'],
            'signups' => ['label' => 'Signups', 'url' => 'list-builder/signups'],
            'sources' => ['label' => 'Sources', 'url' => 'list-builder/sources'],
            'destinations' => ['label' => 'Destinations', 'url' => 'list-builder/destinations'],
            // 'connections' => ['label' => 'Connections', 'url' => 'post-to-social/connections'],
        ];
        return $item;
    }

    public function handleSiteRequest()
    {
        // Handler: View::EVENT_BEGIN_BODY
        Event::on(
            View::class,
            View::EVENT_BEGIN_BODY,
            function () {
                Craft::debug(
                    'View::EVENT_BEGIN_BODY',
                    __METHOD__
                );
                // The <body> placeholder tag has just rendered, include any script HTML
                //TODO: Output notification bar here
            }
        );
        // Handler: View::EVENT_END_BODY
        Event::on(
            View::class,
            View::EVENT_END_BODY,
            function () {
                Craft::debug(
                    'View::EVENT_END_BODY',
                    __METHOD__
                );
                // The </body> placeholder tag is about to be rendered, include any script HTML
                echo ListBuilder::$plugin->outputHelpers->renderPopupCode();
            }
        );

        //Inject FE assets
        $this->view->registerAssetBundle(ListBuilderFEAsset::class);

    }

    public function registerCPRoutes()
    {
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['list-builder'] = 'list-builder/dashboard/index';

                $event->rules['list-builder/signups'] = 'list-builder/signup/index';
                $event->rules['list-builder/signups/data'] = 'list-builder/signup/data';
                $event->rules['list-builder/signups/graph'] = 'list-builder/signup/graph';
                $event->rules['list-builder/signups/export'] = 'list-builder/signup/export';

                $event->rules['list-builder/sources'] = 'list-builder/source/index';
                $event->rules['list-builder/sources/<sourceId:\d+>'] = 'list-builder/source/show-edit';
                $event->rules['list-builder/sources/new'] = 'list-builder/source/show-edit';

                $event->rules['list-builder/destinations'] = 'list-builder/destination/index';
                $event->rules['list-builder/destinations/<destinationId:\d+>'] = 'list-builder/destination/show-edit';
                $event->rules['list-builder/destinations/new'] = 'list-builder/destination/show-edit';

                $event->rules['list-builder/destinations/sync'] = 'list-builder/destination/sync-dash';

            }
        );
    }

    public function registerComponentsAndServices()
    {
        $this->setComponents([
            'signupManager' => SignupManager::class,
            'sourceManager' => SourceManager::class,
            'destinationManager' => DestinationManager::class,
            'outputHelpers' => OutputHelpers::class,
        ]);

        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function(Event $e) {
            /** @var CraftVariable $variable */
            $variable = $e->sender;

            // Attach a service:
            $variable->set('listbuilder', OutputHelpers::class);
        });
    }

    // Protected Methods
    // =========================================================================

}
