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

    initStateTable();
});

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
            button.setAttribute('aria-pressed', isActive ? 'true' : 'false');

            const baseLabel = (button.dataset.label || button.textContent).replace(/ ↑| ↓/g, '');
            button.dataset.label = baseLabel;

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