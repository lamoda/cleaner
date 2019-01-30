<?php

declare(strict_types=1);

namespace Lamoda\Cleaner;

use Lamoda\Cleaner\Exception\CleanerException;

interface CleanerInterface
{
    /**
     * @throws CleanerException
     */
    public function clear(): void;
}
