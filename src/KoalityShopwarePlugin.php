<?php

namespace Koality\ShopwarePlugin;

use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\Config\Exception\LoaderLoadException;
use Symfony\Component\Routing\RouteCollectionBuilder;
use Shopware\Core\Framework\Plugin;

class KoalityShopwarePlugin extends Plugin
{
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

    private function createGuid()
    {
        if (function_exists('com_create_guid') === true) {
            return trim(com_create_guid(), '{}');
        }

        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }
}
