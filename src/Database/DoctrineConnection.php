<?php

namespace TCG\Voyager\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\AbstractMySQLDriver;
use Doctrine\DBAL\Driver\AbstractPostgreSQLDriver;
use Doctrine\DBAL\Driver\AbstractSQLiteDriver;
use Doctrine\DBAL\Driver\PDO\Connection as PDOConnection;
use Illuminate\Database\Connection as LaravelConnection;
use PDO;

/**
 * Laravel 11+ no longer ships a Doctrine DBAL connection, so we build one
 * on top of the PDO instance Laravel already holds (sharing transactions and sqlite :memory:).
 */
abstract class DoctrineConnection
{
    /**
     * @var \WeakMap<PDO, Connection>|null
     */
    protected static $connections;

    public static function for(LaravelConnection $connection): Connection
    {
        $pdo = $connection->getPdo();

        static::$connections ??= new \WeakMap();

        return static::$connections[$pdo] ??= new Connection([], static::driver($connection->getDriverName(), $pdo));
    }

    protected static function driver(string $driverName, PDO $pdo): Driver
    {
        return match ($driverName) {
            'mysql', 'mariadb' => new class($pdo) extends AbstractMySQLDriver {
                public function __construct(private PDO $pdo)
                {
                }

                public function connect(array $params): PDOConnection
                {
                    return new PDOConnection($this->pdo);
                }
            },
            'pgsql' => new class($pdo) extends AbstractPostgreSQLDriver {
                public function __construct(private PDO $pdo)
                {
                }

                public function connect(array $params): PDOConnection
                {
                    return new PDOConnection($this->pdo);
                }
            },
            'sqlite' => new class($pdo) extends AbstractSQLiteDriver {
                public function __construct(private PDO $pdo)
                {
                }

                public function connect(array $params): PDOConnection
                {
                    return new PDOConnection($this->pdo);
                }
            },
            default => throw new \Exception("Database driver {$driverName} is not supported by Voyager"),
        };
    }
}
