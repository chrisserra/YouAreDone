<?php

declare(strict_types=1);

use App\Controllers\CandidateController;
use App\Controllers\RaceController;
use App\Core\Router;

require_once dirname(__DIR__) . '/bootstrap.php';

ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$path = normalize_path($requestUri);

$router = new Router();

$router->get('/', static function (): void {
    render_view('home', [
        'pageTitle' => 'YouAreDone.org',
        'metaDescription' => 'Track candidates, races, rankings, and election details.',
        'canonicalUrl' => absolute_url('/'),
    ]);
});

$router->get('/candidate/{slug:[a-z0-9-]+}', static function (array $params): void {
    $slug = trim((string)($params['slug'] ?? ''));

    if ($slug === '') {
        not_found($_SERVER['REQUEST_URI'] ?? '/');
        return;
    }

    (new CandidateController())->show($slug);
});

$router->get('/races/{stateSlug:[a-z0-9-]+}/{officeSlug:[a-z0-9-]+}/{year:\d{4}}', static function (array $params): void {
    $stateSlug = trim((string)($params['stateSlug'] ?? ''));
    $officeSlug = trim((string)($params['officeSlug'] ?? ''));
    $year = (int)($params['year'] ?? 0);

    if ($stateSlug === '' || $officeSlug === '' || $year <= 0) {
        not_found($_SERVER['REQUEST_URI'] ?? '/');
        return;
    }

    (new RaceController())->show($stateSlug, $officeSlug, $year, null);
});

$router->get('/races/{stateSlug:[a-z0-9-]+}/{officeSlug:[a-z0-9-]+}/{year:\d{4}}/district-{district:\d+}', static function (array $params): void {
    $stateSlug = trim((string)($params['stateSlug'] ?? ''));
    $officeSlug = trim((string)($params['officeSlug'] ?? ''));
    $year = (int)($params['year'] ?? 0);
    $district = (int)($params['district'] ?? 0);

    if ($stateSlug === '' || $officeSlug === '' || $year <= 0 || $district <= 0) {
        not_found($_SERVER['REQUEST_URI'] ?? '/');
        return;
    }

    (new RaceController())->show($stateSlug, $officeSlug, $year, $district);
});

$router->setNotFound(static function (): void {
    not_found($_SERVER['REQUEST_URI'] ?? '/');
});

try {
    $router->dispatch($requestMethod, $path);
} catch (Throwable $e) {
    server_error($path, $e);
}