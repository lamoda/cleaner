<?php

declare(strict_types=1);

namespace Lamoda\CleanerTests\DB;

use Lamoda\Cleaner\DB\Config\DBCleanerConfigFactory;
use Lamoda\Cleaner\DB\Config\QueryConfig;
use PHPUnit\Framework\TestCase;

class DBCleanerConfigFactoryTest extends TestCase
{
    /**
     * @dataProvider queryConfigSchemaProvider
     *
     * @param array  $config
     * @param array  $expectedTransactional
     * @param string $expectedQuery
     * @param array  $expectedParameters
     * @param array  $expectedTypes
     */
    public function testCreateQueriesConfig(
        array $config,
        bool $expectedTransactional,
        int $expectedQueryCount,
        string $expectedQuery,
        array $expectedParameters,
        array $expectedTypes
    ): void {
        $config = DBCleanerConfigFactory::create($config);

        $this->assertSame($expectedTransactional, $config->isTransactional());

        $queries = $config->getQueries();
        $this->assertCount($expectedQueryCount, $queries);

        $query = reset($queries);
        $this->assertInstanceOf(QueryConfig::class, $query);
        $this->assertSame($expectedQuery, $query->getQuery());
        $this->assertSame($expectedParameters, $query->getParameters());
        $this->assertSame($expectedTypes, $query->getTypes());
    }

    public function queryConfigSchemaProvider(): array
    {
        $query = "DELETE * FROM big_table WHERE created_at < NOW() - (:interval || ' days')::interval";
        $parameters = ['interval' => 90];
        $types = ['integer'];

        return [
            'single query' => [
                'config' => [
                    'transactional' => 'false',
                    'query' => $query,
                    'parameters' => $parameters,
                ],
                'expectedTransactional' => false,
                'expectedQueryCount' => 1,
                'expectedQuery' => $query,
                'expectedParameters' => $parameters,
                'expectedTypes' => [],
            ],
            'list of queries' => [
                'config' => [
                    'transactional' => false,
                    'queries' => [
                        [
                            'query' => $query,
                            'parameters' => $parameters,
                        ],
                    ],
                ],
                'expectedTransactional' => false,
                'expectedQueryCount' => 1,
                'expectedQuery' => $query,
                'expectedParameters' => $parameters,
                'expectedTypes' => [],
            ],
            'list of queries with transaction' => [
                'config' => [
                    'transactional' => 1,
                    'queries' => [
                        [
                            'query' => $query,
                            'parameters' => $parameters,
                        ],
                        [
                            'query' => $query,
                        ],
                    ],
                ],
                'expectedTransactional' => true,
                'expectedQueryCount' => 2,
                'expectedQuery' => $query,
                'expectedParameters' => $parameters,
                'expectedTypes' => [],
            ],
            'typed parameters' => [
                'config' => [
                    'query' => $query,
                    'parameters' => $parameters,
                    'types' => $types,
                ],
                'expectedTransactional' => true,
                'expectedQueryCount' => 1,
                'expectedQuery' => $query,
                'expectedParameters' => $parameters,
                'expectedTypes' => $types,
            ],
        ];
    }

    /**
     * @dataProvider badConfigProvider
     *
     * @param array $config
     */
    public function testWhenBadConfigShouldThrowException(array $config): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Not found query for DB cleaner config');

        DBCleanerConfigFactory::create($config);
    }

    public function badConfigProvider(): array
    {
        return [
            'query not defined' => [
                'config' => [
                    'queries' => [
                        [
                            'query' => null,
                        ],
                    ],
                ],
            ],
        ];
    }
}
