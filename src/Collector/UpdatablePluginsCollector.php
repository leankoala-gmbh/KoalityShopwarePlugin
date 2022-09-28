<?php

namespace Koality\ShopwarePlugin\Collector;

use GuzzleHttp\Exception\ClientException;
use Koality\ShopwarePlugin\Formatter\Result;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Store\Exception\StoreApiException;
use Shopware\Core\Framework\Store\Services\AbstractExtensionDataProvider;
use Shopware\Core\Framework\Store\Services\StoreClient;
use Shopware\Core\Framework\Store\Struct\ExtensionCollection;

class UpdatablePluginsCollector implements Collector
{
    /**
     * @var array
     */
    private $pluginConfig = [];

    /**
     * @var Context
     */
    private $context;

    /**
     * @var StoreClient
     */
    private $storeClient;

    /**
     * @var AbstractExtensionDataProvider
     */
    private $extensionDataProvider;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        array $pluginConfig,
        AbstractExtensionDataProvider $extensionDataProvider,
        Context $context,
        StoreClient $storeClient,
        LoggerInterface $logger = null
    ) {
        $this->pluginConfig = $pluginConfig;
        $this->extensionDataProvider = $extensionDataProvider;
        $this->context = $context;
        $this->storeClient = $storeClient;
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * @inheritdoc
     */
    public function getResult(): Result
    {
        $updatablePlugins = $this->getUpdatablePlugins();

        $updatablePluginsCount = count($updatablePlugins);

        if (array_key_exists('pluginsUpdatable', $this->pluginConfig)) {
            $maxUpdatablePluginsCount = $this->pluginConfig['pluginsUpdatable'];
        } else {
            $maxUpdatablePluginsCount = 0;
        }

        if ($maxUpdatablePluginsCount < $updatablePluginsCount) {
            $pluginResult = new Result(Result::STATUS_FAIL, Result::KEY_PLUGINS_UPDATABLE, 'Too many plugins need to be updated.');
        } else {
            $pluginResult = new Result(Result::STATUS_PASS, Result::KEY_PLUGINS_UPDATABLE, 'Not too many plugins need to be updated.');
        }

        $pluginResult->addAttribute('plugins', $updatablePlugins);

        $pluginResult->setLimit($maxUpdatablePluginsCount);
        $pluginResult->setObservedValue($updatablePluginsCount);
        $pluginResult->setObservedValueUnit('plugins');
        $pluginResult->setLimitType(Result::LIMIT_TYPE_MAX);
        $pluginResult->setType(Result::TYPE_TIME_SERIES_NUMERIC);
        $pluginResult->setObservedValuePrecision(0);

        return $pluginResult;
    }

    /**
     * Return a list of plugins that can be updated.
     *
     * @return array
     */
    private function getUpdatablePlugins()
    {
        /** @var Plugin[] $plugins */

        $extensions = $this->extensionDataProvider->getInstalledExtensions(new Context(new SystemSource()), false);

        $extensionCollection = new ExtensionCollection($extensions);;

        try {
            $updateList = $this->storeClient->getExtensionUpdateList(
                $extensionCollection,
                new Context(new SystemSource())
            );
        } catch (StoreApiException|ClientException $exception) {
            $this->logger->error('Got error while fetching extension update list', ['exception' => $exception]);

            return [];
        }

        $updatablePlugins = [];

        foreach ($updateList as $updateElement) {
            $pluginVars = $updateElement->getVars();
            $updatablePlugins[] = [
                'name' => $pluginVars['name'],
                'label' => $pluginVars['label']
            ];
        }

        return $updatablePlugins;
    }
}
