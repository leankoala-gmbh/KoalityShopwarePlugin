<?php

namespace Koality\ShopwarePlugin\Collector;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Koality\ShopwarePlugin\Formatter\Result;

class NewsletterSubscriptionCollector implements Collector
{
    /**
     * @var array
     */
    private $pluginConfig;

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(array $pluginConfig, Connection $connection)
    {
        $this->connection = $connection;
        $this->pluginConfig = $pluginConfig;
    }

    /**
     * @inheritDoc
     *
     * @throws DBALException
     */
    public function getResult(): Result
    {
        $newsletterSubscriptions = $this->getNewsletterRegistrations();

        if (array_key_exists('newsletterSubscriptions', $this->pluginConfig)) {
            $minNewsletterSubscriptions = $this->pluginConfig['newsletterSubscriptions'];
        } else {
            $minNewsletterSubscriptions = 0;
        }

        if ($newsletterSubscriptions < $minNewsletterSubscriptions) {
            $newsletterResult = new Result(Result::STATUS_FAIL, Result::KEY_NEWSLETTER_TOO_FEW, 'There were too few newsletter subscriptions yesterday.');
        } else {
            $newsletterResult = new Result(Result::STATUS_PASS, Result::KEY_NEWSLETTER_TOO_FEW, 'There were enough newsletter subscriptions yesterday.');
        }

        $newsletterResult->setLimit($minNewsletterSubscriptions);
        $newsletterResult->setObservedValue($newsletterSubscriptions);
        $newsletterResult->setObservedValueUnit('newsletters');
        $newsletterResult->setLimitType(Result::LIMIT_TYPE_MIN);
        $newsletterResult->setType(Result::TYPE_TIME_SERIES_NUMERIC);

        return $newsletterResult;
    }

    /**
     * @return int | boolean
     *
     * @throws DBALException
     */
    private function getNewsletterRegistrations(): int
    {
        $sql = 'SELECT count(*) FROM newsletter_recipient WHERE created_at >= ? AND created_at < ?';

        $statement = $this->connection->executeQuery($sql, [
            date('Y.m.d', strtotime('-1 days')),
            date('Y.m.d')
        ]);

        $count = $statement->fetchColumn();

        if ($count === false) {
            return -1;
        } else {
            return $count;
        }
    }
}
