<?php

namespace Koality\ShopwarePlugin\Formatter;

/**
 * Class Result
 *
 * @package Koality\ShopwarePlugin\Formatter
 *
 * @author Nils Langner <nils.langner@leankoala.com>
 * created 2020-12-23
 */
class Result
{
    const KEY_ORDERS_TOO_FEW = 'orders.too_few';
    const KEY_CARTS_OPEN_TOO_MANY = 'carts.open.too_many';
    const KEY_PRODUCTS_ACTIVE = 'products.active';

    /** The allowed result statuses */
    const STATUS_PASS = 'pass';
    const STATUS_FAIL = 'fail';

    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $message;

    /**
     * @var string
     */
    private $key;

    /**
     * @var int
     */
    private $limit;

    /**
     * @var mixed
     */
    private $observedValue;

    /**
     * @var string
     */
    private $observedValueUnit;

    /**
     * Result constructor.
     *
     * @param string $status
     * @param string $key
     * @param string $message
     */
    public function __construct(string $status, string $key, string $message)
    {
        $this->status = $status;
        $this->message = $message;
        $this->key = $key;
    }

    /**
     * Return the results status. Can be fail or pass.
     *
     * Use the class constants for checking the status.
     *
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Return the results message.
     *
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Return the results unique key.
     *
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Get the limit of the metric that was checked.
     *
     * This field is optional.
     *
     * @return int|null
     */
    public function getLimit(): ?int
    {
        return $this->limit;
    }

    /**
     * Set the limit of the metric that was checked.
     *
     * @param int $limit
     */
    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    /**
     * Get the current value of the checked metric.
     *
     * This field is optional.
     *
     * @return mixed
     */
    public function getObservedValue()
    {
        return $this->observedValue;
    }

    /**
     * Set the current value if the metric that is checked.
     *
     * @param mixed $observedValue
     */
    public function setObservedValue($observedValue)
    {
        $this->observedValue = $observedValue;
    }

    /**
     * Return the unit of the observed value.
     *
     * @return string
     */
    public function getObservedValueUnit()
    {
        return $this->observedValueUnit;
    }

    /**
     * Set the unit of the observed value.
     *
     * @param string $observedValueUnit
     */
    public function setObservedValueUnit(string $observedValueUnit)
    {
        $this->observedValueUnit = $observedValueUnit;
    }
}
