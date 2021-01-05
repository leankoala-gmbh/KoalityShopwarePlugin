<?php

namespace Koality\ShopwarePlugin\Collector;

use Koality\ShopwarePlugin\Formatter\Result;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;

/**
 * Class MinOrdersCollector
 *
 * @package Koality\ShopwarePlugin\Collector
 *
 * @author Nils Langner <nils.langner@leankoala.com>
 * created 2020-12-28
 */
class CountOrdersCollector implements Collector
{
    /**
     * @var array
     */
    private $pluginConfig = [];

    /**
     * @var EntityRepository
     */
    private $orderRepository;

    /**
     * @var Context
     */
    private $context;

    /**
     * OpenCartsCollector constructor.
     *
     * @param array $pluginConfig
     * @param Context $context
     * @param EntityRepository $orderRepository
     */
    public function __construct(array $pluginConfig, Context $context, EntityRepository $orderRepository)
    {
        $this->pluginConfig = $pluginConfig;
        $this->orderRepository = $orderRepository;
        $this->context = $context;
    }

    /**
     * @inheritdoc
     */
    public function getResult(): Result
    {
        $salesThreshold = $this->getCurrentSalesThreshold();

        $currentOrdersCount = $this->getLastHourOrderCount();

        if ($currentOrdersCount < $salesThreshold) {
            $orderResult = new Result(Result::STATUS_FAIL, Result::KEY_ORDERS_TOO_FEW, 'There were too few orders within the last hour.');
        } else {
            $orderResult = new Result(Result::STATUS_PASS, Result::KEY_ORDERS_TOO_FEW, 'There were enough orders within the last hour.');
        }

        $orderResult->setLimit($salesThreshold);
        $orderResult->setObservedValue($currentOrdersCount);
        $orderResult->setObservedValueUnit('orders');
        $orderResult->setLimitType(Result::LIMIT_TYPE_MIN);
        $orderResult->setType(Result::TYPE_TIME_SERIES_NUMERIC);

        return $orderResult;
    }

    /**
     * Return the sales threshold depending on the current time.
     *
     * @return int
     */
    private function getCurrentSalesThreshold(): int
    {
        $config = $this->pluginConfig;

        $currentWeekDay = date('w');
        $isWeekend = ($currentWeekDay == 0 || $currentWeekDay == 6);

        if ($isWeekend && !$config['includeWeekends']) {
            $allowRushHour = false;
        } else {
            $allowRushHour = true;
        }

        if ($allowRushHour && array_key_exists('rushHourBegin', $config) && array_key_exists('rushHourEnd', $config)) {
            $beginHour = (int)substr($config['rushHourBegin'], 0, 2) . substr($config['rushHourBegin'], 3, 2);
            $endHour = (int)substr($config['rushHourEnd'], 0, 2) . substr($config['rushHourEnd'], 3, 2);

            $currentTime = (int)date('Hi');

            if ($currentTime < $endHour && $currentTime > $beginHour) {
                return $config['ordersPerHourRushHour'];
            }
        }

        return $config['ordersPerHourNormal'];
    }

    /**
     * Get the number of orders within the last hour.
     *
     * @return int
     */
    private function getLastHourOrderCount(): int
    {
        $criteria = new Criteria();
        $criteria->addFilter(new RangeFilter('createdAt', [
            RangeFilter::GTE => date('Y-m-d H:i:s', strtotime('- 1 hour'))
        ]));

        $orderRepository = $this->orderRepository;

        /** @var OrderEntity[] $orders */
        $orders = $orderRepository->search($criteria, $this->context);

        return count($orders);
    }
}
