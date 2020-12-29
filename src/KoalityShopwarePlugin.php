<?php

namespace Koality\ShopwarePlugin;

use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\Config\Exception\LoaderLoadException;
use Symfony\Component\Routing\RouteCollectionBuilder;
use Shopware\Core\Framework\Plugin;

/**
 * Class KoalityShopwarePlugin
 *
 * @package Koality\ShopwarePlugin
 *
 * @author Nils Langner <nils.langner@leankoala.com>
 * created 2020-12-28
 */
class KoalityShopwarePlugin extends Plugin
{
    const VERSION = '1.0';

    const CONFIG_KEY_API_KEY = 'apiKey';

    const PLUGIN_NAME = 'KoalityShopwarePlugin';

    /**
     * @param RouteCollectionBuilder $routes
     * @param string $environment
     *
     * @throws LoaderLoadException
     */
    public function configureRoutes(RouteCollectionBuilder $routes, string $environment): void
    {
        $routes->import(__DIR__ . '/Resources/routes.xml');
    }

    /**
     * @inheritDoc
     */
    public function install(InstallContext $installContext): void
    {
        parent::install($installContext);

        $configService = $this->container->get(SystemConfigService::class);
        $fullKey = self::PLUGIN_NAME . '.config.' . self::CONFIG_KEY_API_KEY;
        $configService->set($fullKey, $this->createGuid());
        $configService->savePluginConfiguration($this, true);
    }

    /**
     * Create an UUID for the plugins access.
     *
     * @return string
     */
    private function createGuid()
    {
        if (function_exists('com_create_guid') === true) {
            return trim(com_create_guid(), '{}');
        }

        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }
}
