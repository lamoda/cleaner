<?php

declare(strict_types=1);

namespace Lamoda\Cleaner;

class CleanerCollection implements CleanerInterface
{
    /**
     * @var CleanerInterface[]
     */
    private $cleaners = [];

    public function addCleaner(CleanerInterface $cleaner): self
    {
        $this->cleaners[] = $cleaner;

        return $this;
    }

    public function clear(): void
    {
        foreach ($this->cleaners as $cleaner) {
            $cleaner->clear();
        }
    }
}
