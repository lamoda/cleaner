<?php

declare(strict_types=1);

namespace Lamoda\Cleaner\DB\Config;

class QueryConfig
{
    /**
     * @var string
     */
    private $query;

    /**
     * @var array
     */
    private $parameters;

    /**
     * @var array
     */
    private $types;

    public function __construct(string $query, array $parameters = [], array $types = [])
    {
        $this->query = $query;
        $this->parameters = $parameters;
        $this->types = $types;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getTypes(): array
    {
        return $this->types;
    }
}
