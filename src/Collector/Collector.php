<?php

namespace Koality\ShopwarePlugin\Collector;

use Koality\ShopwarePlugin\Formatter\Result;

/**
 * Interface Collector
 *
 * @package Koality\ShopwarePlugin\Collector
 */
interface Collector
{
    /**
     * Return a health check result for a single criteria.
     *
     * @return Result
     */
    public function getResult(): Result;
}
