<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\App;

class HomeController extends Controller
{
    public function index(): void
    {
        $appName = App::config($this->config, 'app.name', 'YouAreDone.org');
        $appUrl = App::config($this->config, 'app.url', 'https://youaredone.org');

        $this->render('home/under-construction', [
            'pageTitle' => $appName . ' - Under Construction',
            'metaDescription' => 'YouAreDone.org is being rebuilt as an AI-driven election tracking platform.',
            'canonicalUrl' => $appUrl . '/',
            'ogImage' => $appUrl . '/assets/images/og-default.png',
            'appName' => $appName,
        ]);
    }
}