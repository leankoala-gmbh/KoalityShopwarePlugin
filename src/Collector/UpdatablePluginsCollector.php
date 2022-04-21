<?php

namespace Koality\ShopwarePlugin\Collector;

use Koality\ShopwarePlugin\Formatter\Result;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Extension;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\PluginCollection;
use Shopware\Core\Framework\Store\Services\AbstractExtensionDataProvider;
use Shopware\Core\Framework\Store\Services\StoreClient;
use Shopware\Core\Framework\Store\Struct\ExtensionCollection;
use Shopware\Core\Framework\Store\Struct\StoreUpdateStruct;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class UpdateablePluginsCollector
 *
 * @package Koality\ShopwarePlugin\Collector
 *
 * @author Nils Langner <nils.langner@leankoala.com>
 * created 2020-12-29
 */
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

    private $extensionDataProvider;

    /**
     * CountOrdersCollector constructor.
     *
     * @param array $pluginConfig
     * @param AbstractExtensionDataProvider $extensionDataProvider
     * @param Context $context
     * @param StoreClient $storeClient
     */
    public function __construct(array $pluginConfig, AbstractExtensionDataProvider $extensionDataProvider, Context $context, StoreClient $storeClient)
    {
        $this->pluginConfig = $pluginConfig;
        $this->extensionDataProvider = $extensionDataProvider;
        $this->context = $context;
        $this->storeClient = $storeClient;
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

        $updateList = $this->storeClient->getExtensionUpdateList(
            $extensionCollection,
            new Context(new SystemSource())
        );

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
