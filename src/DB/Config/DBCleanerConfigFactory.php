<?php

declare(strict_types=1);

namespace Lamoda\Cleaner\DB\Config;

class DBCleanerConfigFactory
{
    public static function create(array $config): DBCleanerConfig
    {
        $params = [
            'transactional' => $config['transactional'] ?? true,
        ];

        if (isset($config['query'])) {
            $params['queries'] = static::prepareQueriesConfig([$config]);
        } else {
            $params['queries'] = static::prepareQueriesConfig($config['queries'] ?? []);
        }

        return new DBCleanerConfig($params);
    }

    /**
     * @param array $configList
     *
     * @return Config\QueryConfig[]
     */
    private static function prepareQueriesConfig(array $configList): array
    {
        $result = [];

        foreach ($configList as $item) {
            if (!isset($item['query']) || !is_string($item['query'])) {
                throw new \InvalidArgumentException('Not found query for DB cleaner config');
            }

            $result[] = new QueryConfig(
                $item['query'],
                $item['parameters'] ?? [],
                $item['types'] ?? []
            );
        }

        return $result;
    }
}
