<?php

namespace Koality\ShopwarePlugin\Formatter;

class Result
{
    const KEY_ORDERS_TOO_FEW = 'orders_too_few';
    const KEY_CARTS_OPEN_TOO_MANY = 'carts_open_too_many';

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
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }
}
