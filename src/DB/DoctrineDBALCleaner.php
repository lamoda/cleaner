<?php

declare(strict_types=1);

namespace Lamoda\Cleaner\DB;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Lamoda\Cleaner\CleanerInterface;
use Lamoda\Cleaner\DB\Config\DBCleanerConfig;
use Lamoda\Cleaner\Exception\CleanerException;

class DoctrineDBALCleaner implements CleanerInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var DBCleanerConfig
     */
    private $config;

    public function __construct(Connection $connection, DBCleanerConfig $config)
    {
        $this->connection = $connection;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): void
    {
        if ($this->config->isTransactional()) {
            $this->connection->transactional(function () {
                $this->executeQueries();
            });
        } else {
            $this->executeQueries();
        }
    }

    /**
     * @throws CleanerException
     */
    private function executeQueries(): void
    {
        foreach ($this->config->getQueries() as $query) {
            try {
                $this->connection->executeQuery(
                    $query->getQuery(),
                    $query->getParameters(),
                    $query->getTypes()
                );
            } catch (DBALException $e) {
                throw new CleanerException($e->getMessage(), $e->getCode(), $e);
            }
        }
    }
}
