<?php

declare(strict_types=1);

$pageTitle = $pageTitle ?? 'Server Error';
$metaDescription = $metaDescription ?? 'Something went wrong.';
$message = $message ?? 'Something went wrong.';
?>

<section class="page-section">
    <div class="card" style="padding: 2rem;">
        <p class="eyebrow">500</p>
        <h1 style="margin-top: 0;">Server Error</h1>
        <p><?= h($message) ?></p>

        <p style="margin-top: 1rem;">
            <a class="button-link" href="/">Go Home</a>
        </p>
    </div>
</section>