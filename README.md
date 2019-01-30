# Lamoda cleaner

Library that provides classes to clear old data from different storages, firstly from databases.

## Installation

1. Install library with composer:
```bash
composer require lamoda/cleaner
```

### Standalone usage

Example of DoctrineDBALCleaner usage, which relies on doctrine/dbal connection.

```php
use Lamoda\Cleaner\DB\Config\DBCleanerConfigFactory;
use Lamoda\Cleaner\DB\DoctrineDBALCleaner;

$config = DBCleanerConfigFactory::create([
    'query' => "DELETE * FROM big_table WHERE created_at < NOW() - (:interval || ' days')::interval",
    'parameters' => [
        'interval' => 90,
    ],
]);

/** @var \Doctrine\DBAL\Connection $connection */
$connection = $entityManager->getConnection();

$cleaner = new DoctrineDBALCleaner($connection, $config);
$cleaner->clear();
```
