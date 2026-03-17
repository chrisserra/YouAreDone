<?php

declare(strict_types=1);

/** @var string $content */
/** @var string|null $pageTitle */
/** @var string|null $metaDescription */
/** @var string|null $canonicalUrl */
/** @var string|null $ogImage */

$pageTitle = $pageTitle ?? 'YouAreDone.org';
$metaDescription = $metaDescription ?? 'Track upcoming primaries, watched races, and candidate accountability.';
$canonicalUrl = $canonicalUrl ?? 'https://youaredone.org/';
$ogImage = $ogImage ?? 'https://youaredone.org/assets/images/og-default.png';

if (!function_exists('h')) {
    function h(?string $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= h($pageTitle) ?></title>
    <meta name="description" content="<?= h($metaDescription) ?>">

    <link rel="canonical" href="<?= h($canonicalUrl) ?>">

    <meta property="og:title" content="<?= h($pageTitle) ?>">
    <meta property="og:description" content="<?= h($metaDescription) ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= h($canonicalUrl) ?>">
    <meta property="og:image" content="<?= h($ogImage) ?>">
    <meta property="og:site_name" content="YouAreDone.org">

    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link
            rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
            integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkR4j8tbtPom6jLJXQ/gh1IsQCxwXKkL4pYg=="
            crossorigin="anonymous"
            referrerpolicy="no-referrer"
    >

    <link rel="stylesheet" href="/assets/css/app.css?v=3">
</head>
<body class="site-body">
<header class="site-header">
    <div class="site-header__inner">
        <a href="/" class="site-logo" aria-label="YouAreDone.org home">
            <img src="/assets/images/youaredone_logo.png?v=3" alt="YouAreDone.org">
        </a>

        <div class="site-header__branding">
            <div class="site-header__tagline">
                Election watch and candidate accountability research
            </div>
        </div>
    </div>
</header>

<main class="site-main">
    <?= $content ?>
</main>
</body>
</html>