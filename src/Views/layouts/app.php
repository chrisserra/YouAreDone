<?php

declare(strict_types=1);

/** @var string $content */
/** @var string|null $pageTitle */
/** @var string|null $metaDescription */
/** @var string|null $canonicalUrl */
/** @var string|null $ogImage */

$version = 25;

$pageTitle = $pageTitle ?? 'YouAreDone.org';
$metaDescription = $metaDescription ?? 'Track upcoming election events, watched races, and candidate accountability.';
$canonicalUrl = $canonicalUrl ?? 'https://youaredone.org/';
$ogImage = $ogImage ?? 'https://youaredone.org/assets/images/og-default.png?v=' . $version;

if (!function_exists('h')) {
    function h(?string $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
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

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-8ESP9QJ9LH"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){ dataLayer.push(arguments); }

        gtag('consent', 'default', {
            analytics_storage: 'denied',
            ad_storage: 'denied',
            ad_user_data: 'denied',
            ad_personalization: 'denied'
        });

        gtag('js', new Date());

        gtag('config', 'G-8ESP9QJ9LH', {
            send_page_view: false
        });
    </script>
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

<div
        class="cookie-banner"
        data-cookie-banner
        hidden
        role="dialog"
        aria-live="polite"
        aria-label="Cookie consent"
>
    <div class="cookie-banner__inner">
        <div class="cookie-banner__content">
            <h2 class="cookie-banner__title">We use cookies</h2>
            <p class="cookie-banner__text">
                We use necessary cookies to make this site work. With your permission, we’d also like to use optional analytics cookies to understand site usage and improve the site.
            </p>
        </div>

        <div class="cookie-banner__actions">
            <button type="button" class="button button-primary" data-cookie-accept-all>
                Accept all
            </button>
            <button type="button" class="button button-secondary" data-cookie-reject-all>
                Reject all
            </button>
            <button type="button" class="button button-secondary" data-cookie-open-settings>
                Customize
            </button>
        </div>
    </div>
</div>

<div
        class="cookie-modal"
        data-cookie-modal
        hidden
        role="dialog"
        aria-modal="true"
        aria-labelledby="cookie-modal-title"
>
    <div class="cookie-modal__backdrop" data-cookie-close-modal></div>

    <div class="cookie-modal__dialog card">
        <div class="cookie-modal__header">
            <div>
                <p class="eyebrow">Cookie Settings</p>
                <h2 id="cookie-modal-title">Manage cookies</h2>
            </div>

            <button
                    type="button"
                    class="cookie-modal__close"
                    data-cookie-close-modal
                    aria-label="Close cookie settings"
            >
                <i class="fa-solid fa-xmark" aria-hidden="true"></i>
            </button>
        </div>

        <div class="cookie-modal__body">
            <p class="cookie-modal__copy">
                Necessary cookies are always on because they help the site function properly. You can choose whether to allow optional analytics cookies.
            </p>

            <div class="cookie-settings">
                <div class="cookie-setting">
                    <div class="cookie-setting__text">
                        <h3>Strictly necessary</h3>
                        <p>Required for core site functionality and security.</p>
                    </div>

                    <label class="cookie-toggle">
                        <input type="checkbox" checked disabled>
                        <span class="cookie-toggle__slider"></span>
                        <span class="cookie-toggle__label">Always on</span>
                    </label>
                </div>

                <div class="cookie-setting">
                    <div class="cookie-setting__text">
                        <h3>Analytics</h3>
                        <p>Helps us understand how visitors use the site so we can improve it.</p>
                    </div>

                    <label class="cookie-toggle">
                        <input
                                type="checkbox"
                                data-cookie-analytics-toggle
                        >
                        <span class="cookie-toggle__slider"></span>
                        <span class="cookie-toggle__label">Optional</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="cookie-modal__footer">
            <button type="button" class="button button-secondary" data-cookie-reject-all>
                Reject all
            </button>
            <button type="button" class="button button-secondary" data-cookie-save-preferences>
                Save preferences
            </button>
            <button type="button" class="button button-primary" data-cookie-accept-all>
                Accept all
            </button>
        </div>
    </div>
</div>

<button
        type="button"
        class="cookie-settings-link"
        data-cookie-open-settings
        aria-label="Open cookie settings"
>
    Cookie settings
</button>

<script src="/assets/js/app.js?v=<?= $version ?>"></script>
</body>
</html>