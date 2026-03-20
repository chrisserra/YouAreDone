document.addEventListener('DOMContentLoaded', function () {
    initShareMenu();
    initCopyLinks();
    initRaceToggles();
    initStateTable();
    initClickableRows();
    initClickableCards();
    initCalendarOverflow();
    initCookieConsent();
});

function initClickableRows() {
    document.addEventListener('click', function (event) {
        const row = event.target.closest('tr[data-href]');

        if (!row) {
            return;
        }

        if (event.target.closest('a, button, input, select, label, summary, details')) {
            return;
        }

        const href = row.getAttribute('data-href');

        if (href) {
            window.location.href = href;
        }
    });
}

function initClickableCards() {
    document.addEventListener('click', function (event) {
        const card = event.target.closest('.event-candidate--clickable[data-href]');

        if (!card) {
            return;
        }

        if (event.target.closest('a, button, input, select, label, summary, details')) {
            return;
        }

        const href = card.getAttribute('data-href');

        if (href) {
            window.location.href = href;
        }
    });

    document.addEventListener('keydown', function (event) {
        const card = event.target.closest('.event-candidate--clickable[data-href]');

        if (!card) {
            return;
        }

        if (event.key !== 'Enter' && event.key !== ' ') {
            return;
        }

        event.preventDefault();

        const href = card.getAttribute('data-href');

        if (href) {
            window.location.href = href;
        }
    });
}

function initShareMenu() {
    const shareToggle = document.querySelector('[data-share-toggle]');
    const shareMenu = document.querySelector('[data-share-menu]');

    if (!shareToggle || !shareMenu) {
        return;
    }

    function closeShareMenu() {
        shareMenu.hidden = true;
        shareToggle.setAttribute('aria-expanded', 'false');
    }

    function openShareMenu() {
        shareMenu.hidden = false;
        shareToggle.setAttribute('aria-expanded', 'true');
    }

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

function initCopyLinks() {
    document.addEventListener('click', async function (event) {
        const button = event.target.closest('[data-copy-link]');

        if (!button) {
            return;
        }

        const url = button.getAttribute('data-copy-link') || '';

        if (!url) {
            return;
        }

        const originalHtml = button.innerHTML;

        try {
            await navigator.clipboard.writeText(url);
            button.innerHTML = '<i class="fa-solid fa-check" aria-hidden="true"></i><span>Copied</span>';

            window.setTimeout(function () {
                button.innerHTML = originalHtml;
            }, 1400);
        } catch (error) {
            window.prompt('Copy this link:', url);
        }
    });
}

function initRaceToggles() {
    document.addEventListener('click', function (event) {
        const button = event.target.closest('[data-race-toggle]');

        if (!button) {
            return;
        }

        const controlsId = button.getAttribute('aria-controls') || '';
        let container = null;

        if (controlsId !== '') {
            container = document.getElementById(controlsId);
        }

        if (!container) {
            container = button.closest('.event-race')?.querySelector('.event-candidates');
        }

        if (!container) {
            return;
        }

        const hiddenCards = container.querySelectorAll('[data-hidden-candidate="true"]');
        const isExpanded = button.getAttribute('aria-expanded') === 'true';

        hiddenCards.forEach(function (card) {
            card.style.display = isExpanded ? 'none' : 'block';
        });

        button.setAttribute('aria-expanded', isExpanded ? 'false' : 'true');
        button.textContent = isExpanded
            ? (button.dataset.showText || 'View other candidates')
            : (button.dataset.hideText || 'Hide other candidates');
    });
}

function initStateTable() {
    const table = document.getElementById('state-table');
    const searchInput = document.getElementById('state-table-search');
    const typeFilter = document.getElementById('state-table-type-filter');
    const officeFilter = document.getElementById('state-table-office-filter');
    const sortButtons = document.querySelectorAll('.state-table__sort');

    if (!table) {
        return;
    }

    const tbody = table.querySelector('tbody');

    if (!tbody) {
        return;
    }

    let currentSortKey = 'date';
    let currentSortDirection = 'asc';

    function getRows() {
        return Array.from(tbody.querySelectorAll('tr'));
    }

    function matchesFilters(row) {
        const query = (searchInput ? searchInput.value : '').trim().toLowerCase();
        const selectedType = (typeFilter ? typeFilter.value : '').trim().toLowerCase();
        const selectedOffice = (officeFilter ? officeFilter.value : '').trim().toLowerCase();

        const state = (row.dataset.state || '').toLowerCase();
        const type = (row.dataset.type || '').toLowerCase();
        const offices = (row.dataset.offices || '').toLowerCase();
        const rowText = [state, type, offices].join(' ');

        if (query !== '' && !rowText.includes(query)) {
            return false;
        }

        if (selectedType !== '' && type !== selectedType) {
            return false;
        }

        if (selectedOffice !== '' && !offices.includes(selectedOffice)) {
            return false;
        }

        return true;
    }

    function compareRows(a, b) {
        const aValue = (a.dataset[currentSortKey] || '').toLowerCase();
        const bValue = (b.dataset[currentSortKey] || '').toLowerCase();

        if (currentSortKey === 'date') {
            if (aValue < bValue) {
                return currentSortDirection === 'asc' ? -1 : 1;
            }

            if (aValue > bValue) {
                return currentSortDirection === 'asc' ? 1 : -1;
            }

            const aState = (a.dataset.state || '').toLowerCase();
            const bState = (b.dataset.state || '').toLowerCase();

            if (aState < bState) {
                return -1;
            }

            if (aState > bState) {
                return 1;
            }

            return 0;
        }

        if (aValue < bValue) {
            return currentSortDirection === 'asc' ? -1 : 1;
        }

        if (aValue > bValue) {
            return currentSortDirection === 'asc' ? 1 : -1;
        }

        return 0;
    }

    function updateSortButtons() {
        sortButtons.forEach(function (button) {
            const isActive = button.dataset.sortKey === currentSortKey;
            const baseLabel = (button.dataset.label || button.textContent).replace(/ ↑| ↓/g, '');

            button.dataset.label = baseLabel;
            button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            button.textContent = isActive
                ? baseLabel + (currentSortDirection === 'asc' ? ' ↑' : ' ↓')
                : baseLabel;
        });
    }

    function renderTable() {
        const rows = getRows();

        rows.sort(compareRows);

        rows.forEach(function (row) {
            row.style.display = matchesFilters(row) ? '' : 'none';
            tbody.appendChild(row);
        });

        updateSortButtons();
    }

    sortButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const nextKey = button.dataset.sortKey || 'date';

            if (currentSortKey === nextKey) {
                currentSortDirection = currentSortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                currentSortKey = nextKey;
                currentSortDirection = 'asc';
            }

            renderTable();
        });
    });

    if (searchInput) {
        searchInput.addEventListener('input', renderTable);
    }

    if (typeFilter) {
        typeFilter.addEventListener('change', renderTable);
    }

    if (officeFilter) {
        officeFilter.addEventListener('change', renderTable);
    }

    renderTable();
}

function initCalendarOverflow() {
    const overflowItems = Array.from(document.querySelectorAll('.calendar-overflow'));

    if (overflowItems.length === 0) {
        return;
    }

    overflowItems.forEach(function (detailsEl) {
        detailsEl.addEventListener('toggle', function () {
            if (!detailsEl.open) {
                return;
            }

            overflowItems.forEach(function (otherEl) {
                if (otherEl !== detailsEl) {
                    otherEl.open = false;
                }
            });
        });
    });

    document.addEventListener('click', function (event) {
        overflowItems.forEach(function (detailsEl) {
            if (!detailsEl.open) {
                return;
            }

            if (!detailsEl.contains(event.target)) {
                detailsEl.open = false;
            }
        });
    });

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') {
            return;
        }

        overflowItems.forEach(function (detailsEl) {
            detailsEl.open = false;
        });
    });
}

function initCookieConsent() {
    const storageKey = 'youaredone_cookie_consent';
    const consentVersion = 1;

    const banner = document.querySelector('[data-cookie-banner]');
    const modal = document.querySelector('[data-cookie-modal]');
    const analyticsToggle = document.querySelector('[data-cookie-analytics-toggle]');

    const acceptButtons = document.querySelectorAll('[data-cookie-accept-all]');
    const rejectButtons = document.querySelectorAll('[data-cookie-reject-all]');
    const openSettingsButtons = document.querySelectorAll('[data-cookie-open-settings]');
    const savePreferencesButton = document.querySelector('[data-cookie-save-preferences]');
    const closeModalButtons = document.querySelectorAll('[data-cookie-close-modal]');

    if (!banner || !modal) {
        return;
    }

    function getStoredConsent() {
        try {
            const raw = window.localStorage.getItem(storageKey);

            if (!raw) {
                return null;
            }

            const parsed = JSON.parse(raw);

            if (!parsed || typeof parsed !== 'object') {
                return null;
            }

            if (parsed.version !== consentVersion) {
                return null;
            }

            return parsed;
        } catch (error) {
            return null;
        }
    }

    function storeConsent(consent) {
        try {
            window.localStorage.setItem(storageKey, JSON.stringify(consent));
        } catch (error) {
            // Ignore storage failures.
        }
    }

    function buildConsent(analyticsEnabled) {
        return {
            version: consentVersion,
            timestamp: new Date().toISOString(),
            categories: {
                necessary: true,
                analytics: !!analyticsEnabled
            }
        };
    }

    function hideBanner() {
        banner.hidden = true;
        syncSettingsLinkVisibility();
    }

    function showBanner() {
        banner.hidden = false;
        syncSettingsLinkVisibility();
    }

    const settingsLink = document.querySelector('.cookie-settings-link');

    function syncSettingsLinkVisibility() {
        if (!settingsLink) {
            return;
        }

        settingsLink.hidden = !banner.hidden;
    }

    function openModal() {
        modal.hidden = false;
        document.body.classList.add('cookie-modal-open');

        if (analyticsToggle) {
            window.setTimeout(function () {
                analyticsToggle.focus();
            }, 0);
        }
    }

    function closeModal() {
        modal.hidden = true;
        document.body.classList.remove('cookie-modal-open');
    }

    function syncUiFromConsent(consent) {
        const analyticsEnabled = !!(consent && consent.categories && consent.categories.analytics);

        if (analyticsToggle) {
            analyticsToggle.checked = analyticsEnabled;
        }
    }

    function applyConsent(consent) {
        syncUiFromConsent(consent);

        const hasAnalyticsConsent = !!(consent && consent.categories && consent.categories.analytics);

        document.documentElement.dataset.cookieAnalytics = hasAnalyticsConsent ? 'granted' : 'denied';

        document.dispatchEvent(new CustomEvent('youaredone:cookie-consent-updated', {
            detail: consent
        }));
    }

    function saveAndApply(consent) {
        storeConsent(consent);
        applyConsent(consent);
        hideBanner();
        closeModal();
    }

    function handleAcceptAll() {
        saveAndApply(buildConsent(true));
    }

    function handleRejectAll() {
        saveAndApply(buildConsent(false));
    }

    function handleSavePreferences() {
        const analyticsEnabled = analyticsToggle ? analyticsToggle.checked : false;
        saveAndApply(buildConsent(analyticsEnabled));
    }

    acceptButtons.forEach(function (button) {
        button.addEventListener('click', handleAcceptAll);
    });

    rejectButtons.forEach(function (button) {
        button.addEventListener('click', handleRejectAll);
    });

    openSettingsButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const currentConsent = getStoredConsent();
            syncUiFromConsent(currentConsent || buildConsent(false));
            openModal();
        });
    });

    closeModalButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            closeModal();
        });
    });

    if (savePreferencesButton) {
        savePreferencesButton.addEventListener('click', handleSavePreferences);
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !modal.hidden) {
            closeModal();
        }
    });

    const storedConsent = getStoredConsent();

    if (storedConsent) {
        applyConsent(storedConsent);
        hideBanner();
    } else {
        syncUiFromConsent(buildConsent(false));
        showBanner();
    }

    window.YouAreDoneCookieConsent = {
        getConsent: getStoredConsent,
        openSettings: function () {
            const currentConsent = getStoredConsent();
            syncUiFromConsent(currentConsent || buildConsent(false));
            openModal();
        },
        hasAnalyticsConsent: function () {
            const consent = getStoredConsent();
            return !!(consent && consent.categories && consent.categories.analytics);
        }
    };

    syncSettingsLinkVisibility();
}