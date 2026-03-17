<?php

declare(strict_types=1);

$pageTitle = $pageTitle ?? 'Server Error';
$metaDescription = $metaDescription ?? 'Something went wrong.';
$message = $message ?? 'Something went wrong.';
?>

<section class="page-section">
    <div class="card" style="padding: 2rem; text-align: center;">
        <p class="eyebrow">Hey, how you doin'? Sorry, I'm probably working on the site and I uploaded some code that was dependent on some other code that doesn't exist.</p>

        <p>In case you're wondering, this is what the problem is:</p>
        <p style="color: red;"><?= h($message) ?></p>

        <p>P.S.- FDT!</p>

        <p style="margin-top: 1rem;">
            <a class="button-link" href="/">Go Home</a>
        </p>
    </div>
</section>