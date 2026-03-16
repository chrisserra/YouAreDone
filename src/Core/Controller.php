<?php
declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    public function __construct(
        protected Request $request,
        protected Response $response,
        protected array $config = []
    ) {
    }

    protected function render(string $view, array $data = [], string $layout = 'layouts/app'): void
    {
        View::render($view, $data, $layout);
    }
}