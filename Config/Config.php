<?php
// DB credentials — values come from Docker environment variables (docker-compose.yml)
// Falls back to 'localhost' / '' for local dev without Docker
define('DB_SERVER',   getenv('DB_HOST') ?: 'localhost');
define('DB_USERNAME', getenv('DB_USER') ?: 'root');
define('DB_PASSWORD', getenv('DB_PASS') ?: '');
define('DB_DATABASE', getenv('DB_NAME') ?: 'sql_demo');

$db = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
