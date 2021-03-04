<?php

namespace Koality\ShopwarePlugin\Collector;

use Koality\ShopwarePlugin\Formatter\Result;

/**
 * Interface Collector
 *
 * @package Koality\ShopwarePlugin\Collector
 *
 * @author Nils Langner <nils.langner@leankoala.com>
 * created 2020-12-28
 */
interface Collector
{
    /**
     * Return a health check result for a single criteria.
     *
     * @return Result
     */
    public function getResult();
}
