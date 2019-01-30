<?php

declare(strict_types=1);

namespace Lamoda\Cleaner\DB\Config;

class DBCleanerConfig
{
    /**
     * @var bool
     */
    private $transactional;

    /**
     * @var QueryConfig[]
     */
    private $queries;

    public function __construct(array $config)
    {
        $this->transactional = filter_var($config['transactional'] ?? true, FILTER_VALIDATE_BOOLEAN);

        $this->queries = array_map(
            function (QueryConfig $query) {
                return $query;
            },
            $config['queries']
        );
    }

    public function isTransactional(): bool
    {
        return $this->transactional;
    }

    /**
     * @return QueryConfig[]
     */
    public function getQueries(): array
    {
        return $this->queries;
    }
}
