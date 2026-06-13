<?php
namespace SqlInjection;

/**
 * MySQL connection — reads host/credentials from environment variables
 * so it works both locally and inside Docker without code changes.
 */
class MySQLConnection {
    /** @var \PDO|null */
    private $pdo;

    public function connect(): ?\PDO {
        if ($this->pdo === null) {
            $dsn = "mysql:host=" . Config::getHost()
                 . ";dbname="   . Config::getDatabase()
                 . ";charset=utf8mb4";

            $this->pdo = new \PDO(
                $dsn,
                Config::getUser(),
                Config::getPassword(),
                [
                    \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                ]
            );
        }
        return $this->pdo;
    }
}
