<?php

declare(strict_types=1);

function render_view(string $view, array $data = [], int $statusCode = 200): void
{
    http_response_code($statusCode);

    extract($data, EXTR_SKIP);

    ob_start();
    require BASE_PATH . '/src/Views/' . $view . '.php';
    $content = (string)ob_get_clean();

    require BASE_PATH . '/src/Views/layouts/app.php';
    exit;
}

function absolute_url(string $path = '/'): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'youaredone.org';
    $path = parse_url($path, PHP_URL_PATH) ?: '/';
    $path = '/' . ltrim($path, '/');

    return $scheme . '://' . $host . $path;
}

function normalize_path(string $path): string
{
    $path = trim($path);

    if ($path === '') {
        return '/';
    }

    $path = parse_url($path, PHP_URL_PATH) ?: '/';
    $path = preg_replace('#/+#', '/', $path) ?? '/';

    if ($path !== '/' && str_ends_with($path, '/')) {
        $path = rtrim($path, '/');
    }

    return $path;
}

function not_found(string $path): void
{
    render_view('errors/404', [
        'pageTitle' => 'Not Found',
        'metaDescription' => 'The page you requested could not be found.',
        'canonicalUrl' => absolute_url($path),
    ], 404);
}

function server_error(string $path, Throwable $e): void
{
    error_log($e->__toString());

    render_view('errors/500', [
        'pageTitle' => 'Server Error',
        'metaDescription' => 'Something went wrong.',
        'canonicalUrl' => absolute_url($path),
        'message' => (($_ENV['APP_ENV'] ?? 'production') !== 'production')
            ? $e->getMessage()
            : 'Something went wrong.',
    ], 500);
}