<?php

declare(strict_types=1);

$pageTitle = $pageTitle ?? 'Not Found';
$metaDescription = $metaDescription ?? 'The page you requested could not be found.';
?>

<section class="page-section">
    <div class="card" style="padding: 2rem;">
        <p class="eyebrow">404</p>
        <h1 style="margin-top: 0;">Page Not Found</h1>
        <p>The page you requested could not be found.</p>

        <p style="margin-top: 1rem;">
            <a class="button-link" href="/">Go Home</a>
        </p>
    </div>
</section>