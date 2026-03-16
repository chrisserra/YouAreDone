<?php
/** @var string $appName */
$appName = $appName ?? 'YouAreDone.org';
?>
<main class="uc-page">
    <section class="uc-hero">
        <div class="uc-card">
            <div class="uc-logo-wrap">
                <img
                    src="/assets/images/youaredone-logo.png"
                    alt="YouAreDone.org logo"
                    class="uc-logo"
                >
            </div>

            <div class="uc-kicker">Rebuilding the mission</div>

            <h1 class="uc-title">Under Construction</h1>

            <p class="uc-lede">
                <?= htmlspecialchars($appName, ENT_QUOTES, 'UTF-8') ?> is being rebuilt as the place for tracking major U.S. races and candidate developments.
            </p>

            <div class="uc-divider"></div>

            <div class="uc-grid">
                <div class="uc-grid-item">
                    <div class="uc-grid-label">Race Coverage</div>
                    <div class="uc-grid-value">President, Senate, House, Governor</div>
                </div>

                <div class="uc-grid-item">
                    <div class="uc-grid-label">Election Types</div>
                    <div class="uc-grid-value">Primary, General, Special, Runoff, and more</div>
                </div>

                <div class="uc-grid-item">
                    <div class="uc-grid-label">Platform Goal</div>
                    <div class="uc-grid-value">Daily candidate and election updates</div>
                </div>

                <div class="uc-grid-item">
                    <div class="uc-grid-label">Status</div>
                    <div class="uc-grid-value">re-Launching soon</div>
                </div>
            </div>

            <p class="uc-subtext">
                The new site will focus on the races that decide nominees and winners for
                <strong>President</strong>, <strong>U.S. Senate</strong>, <strong>U.S. House</strong>,
                and <strong>Governor</strong>.
            </p>
        </div>
    </section>

    <footer class="uc-footer">
        <div class="uc-footer-inner">
            <span>© <?= date('Y') ?> YouAreDone.org</span>
            <span class="uc-footer-dot">•</span>
            <span>Election tracking in progress...</span>
        </div>
    </footer>
</main>