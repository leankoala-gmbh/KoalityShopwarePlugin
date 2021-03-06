<?php

namespace Koality\ShopwarePlugin\Collector;

use Doctrine\DBAL\Connection;
use Koality\ShopwarePlugin\Formatter\KoalityFormatter;
use RuntimeException;
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

        if (is_null($connection)) {
            throw new RuntimeException('Cannot establish database connection.');
        }

        $pluginRepository = $this->container->get('plugin.repository');

        if (is_null($pluginRepository)) {
            throw new RuntimeException('Cannot find plugin repository.');
        }

        $orderRepository = $this->container->get('order.repository');

        if (is_null($orderRepository)) {
            throw new RuntimeException('Cannot find order repository.');
        }

        $request = $this->container->get('request_stack')->getCurrentRequest();

        // the order of the components also reflects the order the metrics are shown in koality.io
        $this->collectors = [
            new CountOrdersCollector($pluginConfig, $context, $orderRepository),
            new ActiveProductsCollector($pluginConfig, $connection),
            new UpdatablePluginsCollector($pluginConfig, $pluginRepository, $context, $this->storeClient, $request),
            new OpenCartsCollector($pluginConfig, $connection),
            new NewsletterSubscriptionCollector($pluginConfig, $connection),
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
