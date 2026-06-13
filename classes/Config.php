<?php

namespace SqlInjection;

class Config {
    // Reads from Docker environment variables set in docker-compose.yml
    // Falls back to sensible local-dev defaults
    const MYSQL_HOST     = null; // resolved dynamically — see getHost()
    const MYSQL_DATABASE = null;
    const MYSQL_USER     = null;
    const MYSQL_PASSWD   = null;

    public static function getHost(): string {
        return getenv('DB_HOST') ?: 'localhost';
    }

    public static function getDatabase(): string {
        return getenv('DB_NAME') ?: 'sql_demo';
    }

    public static function getUser(): string {
        return getenv('DB_USER') ?: 'root';
    }

    public static function getPassword(): string {
        return getenv('DB_PASS') ?: '';
    }
}
