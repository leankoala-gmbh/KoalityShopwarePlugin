<?php

namespace Koality\ShopwarePlugin\Collector;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Koality\ShopwarePlugin\Formatter\Result;

/**
 * Class ActiveProductsCollector
 *
 * @package Koality\ShopwarePlugin\Collector
 *
 * @author Nils Langner <nils.langner@leankoala.com>
 * created 2020-12-29
 */
class ActiveProductsCollector implements Collector
{
    /**
     * @var array
     */
    private $pluginConfig = [];

    /**
     * @var Connection
     */
    private $connection;

    /**
     * OpenCartsCollector constructor.
     *
     * @param array $pluginConfig
     * @param Connection $connection
     */
    public function __construct(array $pluginConfig, Connection $connection)
    {
        $this->pluginConfig = $pluginConfig;
        $this->connection = $connection;
    }

    /**
     * @inheritdoc
     * @throws DBALException
     */
    public function getResult(): Result
    {
        $activeProductCount = $this->getActiveProductsCount();

        if (array_key_exists('activeProducts', $this->pluginConfig)) {
            $minOpenProjects = $this->pluginConfig['activeProducts'];
        } else {
            $minOpenProjects = 0;
        }

        if ($activeProductCount < $minOpenProjects) {
            $cartResult = new Result(Result::STATUS_FAIL, Result::KEY_PRODUCTS_ACTIVE, 'There are too few active products in your shop.');
        } else {
            $cartResult = new Result(Result::STATUS_PASS, Result::KEY_PRODUCTS_ACTIVE, 'There are enough active products in your shop.');
        }

        $cartResult->setLimit($minOpenProjects);
        $cartResult->setObservedValue($activeProductCount);
        $cartResult->setObservedValueUnit('products');
        $cartResult->setObservedValuePrecision(0);
        $cartResult->setLimitType(Result::LIMIT_TYPE_MIN);
        $cartResult->setType(Result::TYPE_TIME_SERIES_NUMERIC);

        return $cartResult;
    }

    /**
     * Return the number of active products.
     *
     * @return int
     *
     * @throws DBALException
     */
    private function getActiveProductsCount(): int
    {
        $carts = $this->connection->executeQuery('SELECT count(*) FROM product WHERE active = 1 AND parent_id IS NULL;');
        $result = $carts->fetchAll();
        return (int)$result[0]['count(*)'];
    }
}
