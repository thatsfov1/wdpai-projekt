<?php

$envFile = __DIR__ . '/.env';

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {

        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            $value = trim($value, '"\'');
            
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

define('HOST', getenv('DB_HOST') ?: 'db');
define('PORT', getenv('DB_PORT') ?: '5432');
define('DATABASE', getenv('DB_DATABASE') ?: 'db');
define('USERNAME', getenv('DB_USERNAME') ?: 'docker');
define('PASSWORD', getenv('DB_PASSWORD') ?: 'docker');