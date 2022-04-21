<?php

namespace Koality\ShopwarePlugin\Collector;

use Doctrine\DBAL\Connection;
use Koality\ShopwarePlugin\Formatter\KoalityFormatter;
use RuntimeException;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Store\Services\AbstractExtensionDataProvider;
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
     * @var AbstractExtensionDataProvider
     */
    private $extensionDataProvider;

    /**
     * CollectorContainer constructor.
     *
     * @param StoreClient $storeClient
     * @param ContainerInterface $container
     * @param AbstractExtensionDataProvider $extensionDataProvider
     */
    public function __construct(StoreClient $storeClient, ContainerInterface $container, AbstractExtensionDataProvider $extensionDataProvider)
    {
        $this->storeClient = $storeClient;
        $this->container = $container;
        $this->extensionDataProvider = $extensionDataProvider;
    }

    public function init($pluginConfig, Context $context)
    {
        $connection = $this->container->get(Connection::class);

        if (is_null($connection)) {
            throw new RuntimeException('Cannot establish database connection.');
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
            new UpdatablePluginsCollector($pluginConfig, $this->extensionDataProvider, $context, $this->storeClient, $request),
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
