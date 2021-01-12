<?php

namespace Koality\ShopwarePlugin\Collector;

use Doctrine\DBAL\Connection;
use Koality\ShopwarePlugin\Formatter\KoalityFormatter;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Store\Services\StoreClient;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CollectorContainer
 *
 * @package Koality\ShopwarePlugin\Collector
 *
 * @author Nils Langner <nils.langner@leankoala.com>
 * created 2020-12-29
 */
class CollectorContainer
{
    /**
     * @var StoreClient
     */
    private $storeClient;

    /**
     * @var Collector[]
     */
    private $collectors = [];

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * CollectorContainer constructor.
     *
     * @param StoreClient $storeClient
     * @param ContainerInterface $container
     */
    public function __construct(StoreClient $storeClient, ContainerInterface $container)
    {
        $this->storeClient = $storeClient;
        $this->container = $container;
    }

    public function init($pluginConfig, Context $context)
    {
        $connection = $this->container->get(Connection::class);
        $pluginRepository = $this->container->get('plugin.repository');
        $orderRepository = $this->container->get('order.repository');

        $request = $this->container->get('request_stack')->getCurrentRequest();

        $this->collectors = [
            new NewsletterSubscriptionCollector($pluginConfig, $connection),
            new CountOrdersCollector($pluginConfig, $context, $orderRepository),
            new ActiveProductsCollector($pluginConfig, $connection),
            new OpenCartsCollector($pluginConfig, $connection),
            new UpdatablePluginsCollector($pluginConfig, $pluginRepository, $context, $this->storeClient, $request),
        ];
    }

    public function run()
    {
        $formatter = new KoalityFormatter();

        foreach ($this->collectors as $collector) {
            $formatter->addResult($collector->getResult());
        }

        return $formatter;
    }
}
