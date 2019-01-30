<?php

declare(strict_types=1);

namespace Lamoda\CleanerTests;

use Lamoda\Cleaner\CleanerCollection;
use Lamoda\Cleaner\CleanerInterface;
use PHPUnit\Framework\TestCase;

class CleanerCollectionTest extends TestCase
{
    public function testHappyPath(): void
    {
        $cleaner1 = $this->createMock(CleanerInterface::class);
        $cleaner1->expects($this->once())
            ->method('clear');

        $cleaner2 = $this->createMock(CleanerInterface::class);
        $cleaner2->expects($this->once())
            ->method('clear');

        $collection = new CleanerCollection();
        $collection->addCleaner($cleaner1)
            ->addCleaner($cleaner2);
        $collection->clear();
    }

    public function testWhenFirstCleanerFailsShouldNotCallOthers(): void
    {
        $cleaner1 = $this->createMock(CleanerInterface::class);
        $cleaner1->expects($this->once())
            ->method('clear')
            ->will($this->throwException(new \RuntimeException('Cant execute cleaner')));

        $cleaner2 = $this->createMock(CleanerInterface::class);
        $cleaner2->expects($this->never())
            ->method('clear');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cant execute cleaner');

        $collection = new CleanerCollection();
        $collection->addCleaner($cleaner1)
            ->addCleaner($cleaner2);
        $collection->clear();
    }
}
