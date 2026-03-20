<?php

declare(strict_types=1);

/** @var string $content */
/** @var string|null $pageTitle */
/** @var string|null $metaDescription */
/** @var string|null $canonicalUrl */
/** @var string|null $ogImage */

$version = 14;

$pageTitle = $pageTitle ?? 'YouAreDone.org';
$metaDescription = $metaDescription ?? 'Track upcoming election events, watched races, and candidate accountability.';
$canonicalUrl = $canonicalUrl ?? 'https://youaredone.org/';
$ogImage = $ogImage ?? 'https://youaredone.org/assets/images/og-default.png?v='. $version;

if (!function_exists('h')) {
    function h(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

$shareUrl = $canonicalUrl;
$shareTitle = $pageTitle;
$shareText = $pageTitle . ' - ' . $metaDescription;

$encodedUrl = rawurlencode($shareUrl);
$encodedText = rawurlencode($shareText);
$emailSubject = rawurlencode($shareTitle);
$emailBody = rawurlencode($shareText . "\n\n" . $shareUrl);

$facebookShareUrl = 'https://www.facebook.com/sharer/sharer.php?u=' . $encodedUrl;
$xShareUrl = 'https://twitter.com/intent/tweet?text=' . $encodedText . '&url=' . $encodedUrl;
$blueskyShareUrl = 'https://bsky.app/intent/compose?text=' . $encodedText . '%20' . $encodedUrl;
$threadsShareUrl = 'https://www.threads.net/intent/post?text=' . $encodedText . '%20' . $encodedUrl;
$emailShareUrl = 'mailto:?subject=' . $emailSubject . '&body=' . $emailBody;
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
            integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
            crossorigin="anonymous"
            referrerpolicy="no-referrer"
    >
    <link rel="stylesheet" href="/assets/css/app.css?v=<?= $version ?>">
</head>
<body class="site-body">

<header class="site-header">
    <div class="site-header__inner">
        <div class="site-header__top">
            <a href="/" class="site-logo" aria-label="YouAreDone.org home">
                <img src="/assets/images/youaredone_logo.png?v=<?= $version ?>" alt="YouAreDone.org">
            </a>

            <div class="site-header__share">
                <button
                        type="button"
                        class="site-header__share-toggle"
                        data-share-toggle
                        aria-haspopup="true"
                        aria-expanded="false"
                        aria-controls="share-menu"
                >
                    <i class="fa-solid fa-share-nodes" aria-hidden="true"></i>
                    <span>Share</span>
                    <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                </button>

                <div class="site-header__share-menu" id="share-menu" data-share-menu hidden>
                    <button
                            type="button"
                            class="site-header__share-item"
                            data-copy-link="<?= h($shareUrl) ?>"
                    >
                        <i class="fa-solid fa-link"></i>
                        <span>Copy Link</span>
                    </button>

                    <a href="<?= h($emailShareUrl) ?>" class="site-header__share-item">
                        <i class="fa-solid fa-envelope"></i>
                        <span>Email</span>
                    </a>

                    <a href="<?= h($facebookShareUrl) ?>" class="site-header__share-item" target="_blank" rel="noopener noreferrer">
                        <i class="fa-brands fa-facebook"></i>
                        <span>Facebook</span>
                    </a>

                    <a href="<?= h($xShareUrl) ?>" class="site-header__share-item" target="_blank" rel="noopener noreferrer">
                        <i class="fa-brands fa-x-twitter"></i>
                        <span>X</span>
                    </a>

                    <a href="<?= h($blueskyShareUrl) ?>" class="site-header__share-item" target="_blank" rel="noopener noreferrer">
                        <i class="fa-solid fa-cloud"></i>
                        <span>Bluesky</span>
                    </a>

                    <a href="<?= h($threadsShareUrl) ?>" class="site-header__share-item" target="_blank" rel="noopener noreferrer">
                        <i class="fa-brands fa-threads"></i>
                        <span>Threads</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="site-header__branding">
            <div class="site-header__tagline">
                Track elections. Compare candidates. Hold incumbents accountable.
            </div>
        </div>
    </div>
</header>

<main class="site-main">
    <?= $content ?>
</main>

<script src="/assets/js/app.js?v=<?= $version ?>"></script>
</body>
</html>