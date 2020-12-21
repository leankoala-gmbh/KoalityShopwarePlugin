<?php

namespace Koality\ShopwarePlugin\Formatter;

/**
 * Class Result
 *
 * @package Koality\ShopwarePlugin\Formatter
 */
class Result
{
    const KEY_ORDERS_TOO_FEW = 'orders_too_few';
    const KEY_CARTS_OPEN_TOO_MANY = 'carts_open_too_many';

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
    private $currentValue;

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
     * @return int
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
    public function getCurrentValue()
    {
        return $this->currentValue;
    }

    /**
     * Set the current value if the metric that is checked.
     *
     * @param mixed $currentValue
     */
    public function setCurrentValue($currentValue): void
    {
        $this->currentValue = $currentValue;
    }
}
