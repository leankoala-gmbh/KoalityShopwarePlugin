<?php

namespace Koality\ShopwarePlugin\Collector;

use Koality\ShopwarePlugin\Formatter\Result;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Store\Services\StoreClient;
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
     * @var EntityRepositoryInterface
     */
    private $pluginRepository;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var StoreClient
     */
    private $storeClient;

    /**
     * @var Request
     */
    private $request;

    /**
     * CountOrdersCollector constructor.
     *
     * @param array $pluginConfig
     * @param EntityRepositoryInterface $pluginRepository
     * @param Context $context
     * @param StoreClient $storeClient
     * @param Request $request
     */
    public function __construct(array $pluginConfig, EntityRepositoryInterface $pluginRepository, Context $context, StoreClient $storeClient, Request $request)
    {
        $this->pluginConfig = $pluginConfig;
        $this->pluginRepository = $pluginRepository;
        $this->context = $context;
        $this->storeClient = $storeClient;
        $this->request = $request;
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
        $plugins = $this->pluginRepository->search(new Criteria(), $this->context);
        $pluginCollection = new Plugin\PluginCollection($plugins);

        $updateList = $this->storeClient->getUpdatesList(
            null,
            $pluginCollection,
            'de_DE',
            $this->request->getHost(),
            $this->context
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
