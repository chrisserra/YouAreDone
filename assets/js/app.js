document.addEventListener('DOMContentLoaded', function () {
    const shareToggle = document.querySelector('[data-share-toggle]');
    const shareMenu = document.querySelector('[data-share-menu]');

    function closeShareMenu() {
        if (!shareToggle || !shareMenu) {
            return;
        }

        shareMenu.hidden = true;
        shareToggle.setAttribute('aria-expanded', 'false');
    }

    function openShareMenu() {
        if (!shareToggle || !shareMenu) {
            return;
        }

        shareMenu.hidden = false;
        shareToggle.setAttribute('aria-expanded', 'true');
    }

    if (shareToggle && shareMenu) {
        shareToggle.addEventListener('click', function (event) {
            event.stopPropagation();

            if (shareMenu.hidden) {
                openShareMenu();
            } else {
                closeShareMenu();
            }
        });

        document.addEventListener('click', function (event) {
            if (!shareMenu.contains(event.target) && !shareToggle.contains(event.target)) {
                closeShareMenu();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeShareMenu();
            }
        });
    }

    document.addEventListener('click', async function (event) {
        const button = event.target.closest('[data-copy-link]');

        if (!button) {
            return;
        }

        const url = button.getAttribute('data-copy-link') || '';
        if (!url) {
            return;
        }

        const originalText = button.innerHTML;

        try {
            await navigator.clipboard.writeText(url);
            button.innerHTML = '<i class="fa-solid fa-check" aria-hidden="true"></i><span>Copied</span>';

            window.setTimeout(function () {
                button.innerHTML = originalText;
                closeShareMenu();
            }, 1400);
        } catch (error) {
            window.prompt('Copy this link:', url);
        }
    });
});