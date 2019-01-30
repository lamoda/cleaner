<?php

declare(strict_types=1);

namespace Lamoda\CleanerTests\DB;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Lamoda\Cleaner\DB\Config\DBCleanerConfigFactory;
use Lamoda\Cleaner\DB\DoctrineDBALCleaner;
use Lamoda\Cleaner\Exception\CleanerException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DoctrineDBALCleanerTest extends TestCase
{
    /** @var string */
    private $query = "DELETE * FROM queue WHERE created_at < NOW() - (:interval || ' days')::interval";

    /** @var array */
    private $parameters = ['interval' => 90];

    /** @var array */
    private $types = ['integer'];

    public function testHappyPathWithoutTransaction(): void
    {
        $config = DBCleanerConfigFactory::create([
            'transactional' => false,
            'query' => $this->query,
            'parameters' => $this->parameters,
            'types' => $this->types,
        ]);

        $connection = $this->createConnectionMock();
        $connection->expects($this->once())
            ->method('executeQuery')
            ->with($this->query, $this->parameters, $this->types);
        $connection->expects($this->never())
            ->method('beginTransaction');
        $connection->expects($this->never())
            ->method('commit');
        $connection->expects($this->never())
            ->method('rollback');

        $cleaner = new DoctrineDBALCleaner($connection, $config);
        $cleaner->clear();
    }

    public function testMultipleQueriesWithoutTransaction(): void
    {
        $config = DBCleanerConfigFactory::create([
            'transactional' => false,
            'queries' => [
                ['query' => 'query A'],
                ['query' => 'query B'],
            ],
        ]);

        $connection = $this->createConnectionMock();
        $connection->expects($this->exactly(2))
            ->method('executeQuery')
            ->withConsecutive(
                ['query A', [], []],
                ['query B', [], []]
            );
        $connection->expects($this->never())
            ->method('beginTransaction');
        $connection->expects($this->never())
            ->method('commit');
        $connection->expects($this->never())
            ->method('rollback');

        $cleaner = new DoctrineDBALCleaner($connection, $config);
        $cleaner->clear();
    }

    public function testTransactional(): void
    {
        $config = DBCleanerConfigFactory::create([
            'transactional' => true,
            'queries' => [
                ['query' => 'query A'],
                ['query' => 'query B'],
            ],
        ]);

        $connection = $this->createConnectionMock();
        $connection->expects($this->exactly(2))
            ->method('executeQuery')
            ->withConsecutive(
                ['query A', [], []],
                ['query B', [], []]
            );
        $connection->expects($this->once())
            ->method('beginTransaction');
        $connection->expects($this->once())
            ->method('commit');
        $connection->expects($this->never())
            ->method('rollback');

        $cleaner = new DoctrineDBALCleaner($connection, $config);
        $cleaner->clear();
    }

    public function testMultipleQueriesWithException(): void
    {
        $config = DBCleanerConfigFactory::create([
            'transactional' => true,
            'queries' => [
                ['query' => 'query A'],
                ['query' => 'query B'],
            ],
        ]);

        $connection = $this->createConnectionMock();
        $connection->expects($this->once())
            ->method('executeQuery')
            ->with('query A', [], [])
            ->will($this->throwException(new \RuntimeException('DB failure')));

        $connection->expects($this->once())
            ->method('beginTransaction');
        $connection->expects($this->never())
            ->method('commit');
        $connection->expects($this->once())
            ->method('rollback');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('DB failure');

        $cleaner = new DoctrineDBALCleaner($connection, $config);
        $cleaner->clear();
    }

    public function testWhenDoctrineExceptionShouldThrowLibraryException(): void
    {
        $config = DBCleanerConfigFactory::create([
            'transactional' => false,
            'query' => $this->query,
        ]);

        $connection = $this->createConnectionMock();
        $connection->expects($this->once())
            ->method('executeQuery')
            ->will($this->throwException(
                DBALException::invalidTableName('table')
            ));

        $this->expectException(CleanerException::class);

        $cleaner = new DoctrineDBALCleaner($connection, $config);
        $cleaner->clear();
    }

    /**
     * @return Connection | MockObject
     */
    private function createConnectionMock(): MockObject
    {
        return $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'executeQuery',
                'beginTransaction',
                'commit',
                'rollback',
            ])
            ->getMock();
    }
}
