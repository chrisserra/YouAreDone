<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('BASE_PATH', __DIR__);
define('SRC_PATH', BASE_PATH . '/src');
define('VIEW_PATH', SRC_PATH . '/Views');

/*
|--------------------------------------------------------------------------
| Load .env values into $_ENV / $_SERVER / getenv()
|--------------------------------------------------------------------------
*/
$envPath = BASE_PATH . '/.env';

if (is_file($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        [$name, $value] = explode('=', $line, 2);

        $name = trim($name);
        $value = trim($value);

        if (
            (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
            (str_starts_with($value, "'") && str_ends_with($value, "'"))
        ) {
            $value = substr($value, 1, -1);
        }

        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;

        if (getenv($name) === false) {
            putenv($name . '=' . $value);
        }
    }
}

/*
|--------------------------------------------------------------------------
| Load Helper Functions
|--------------------------------------------------------------------------
*/
require_once SRC_PATH . '/Support/helpers.php';

/*
|--------------------------------------------------------------------------
| Autoload App Classes
|--------------------------------------------------------------------------
*/
spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $file = SRC_PATH . '/' . str_replace('\\', '/', $relative) . '.php';

    if (is_file($file)) {
        require_once $file;
    }
});

/*
|--------------------------------------------------------------------------
| Error Handling
|--------------------------------------------------------------------------
*/
set_error_handler(static function (
    int $severity,
    string $message,
    string $file,
    int $line
): bool {
    if (!(error_reporting() & $severity)) {
        return false;
    }

    throw new ErrorException($message, 0, $severity, $file, $line);
});

/*
|--------------------------------------------------------------------------
| Default Timezone
|--------------------------------------------------------------------------
*/
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'America/Los_Angeles');

/*
|--------------------------------------------------------------------------
| HTML Escape Helper
|--------------------------------------------------------------------------
*/
if (!function_exists('h')) {
    function h(?string $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

/*
|--------------------------------------------------------------------------
| Election Date Helper
|--------------------------------------------------------------------------
*/
if (!function_exists('election_date')) {
    function election_date(?string $date): string
    {
        try {
            if (!$date) {
                return '';
            }

            $ts = strtotime($date);

            if ($ts === false) {
                return '';
            }

            $today = date('Y-m-d');
            $value = date('Y-m-d', $ts);

            if ($value === $today) {
                return 'Today';
            }

            return date('F j, Y', $ts);
        } catch (\Throwable $e) {
            return '';
        }
    }
}