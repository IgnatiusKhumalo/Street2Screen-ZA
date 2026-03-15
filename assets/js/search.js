/**
 * Street2Screen ZA - Search & Filter JavaScript
 * Live search and product filtering
 */

document.addEventListener('DOMContentLoaded', function() {
    initLiveSearch();
    initPriceRangeSlider();
    initFilterAutoSubmit();
});

/**
 * Live search with debounce (300ms)
 */
function initLiveSearch() {
    const searchInput = document.getElementById('live-search');
    if (!searchInput) return;

    const resultsContainer = document.getElementById('search-results');
    let debounceTimer;

    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        clearTimeout(debounceTimer);

        if (query.length < 2) {
            if (resultsContainer) resultsContainer.innerHTML = '';
            return;
        }

        debounceTimer = setTimeout(() => {
            fetchSearchResults(query, resultsContainer);
        }, 300);
    });

    // Hide results when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && resultsContainer && !resultsContainer.contains(e.target)) {
            resultsContainer.innerHTML = '';
        }
    });
}

/**
 * Fetch search results from server
 */
function fetchSearchResults(query, container) {
    if (!container) return;

    container.innerHTML = '<div class="p-3 text-center"><div class="spinner-border spinner-border-sm text-primary"></div> Searching...</div>';

    fetch(APP_URL + '/products/index.php?search=' + encodeURIComponent(query) + '&format=json')
        .then(res => res.json())
        .then(data => {
            if (data.products && data.products.length > 0) {
                container.innerHTML = data.products.slice(0, 5).map(p => `
                    <a href="${APP_URL}/products/view.php?id=${p.product_id}" class="d-flex align-items-center p-2 border-bottom text-decoration-none text-dark hover-bg">
                        <img src="${p.image || APP_URL + '/assets/images/placeholder.svg'}" style="width:40px;height:40px;object-fit:cover;border-radius:5px" class="me-2">
                        <div>
                            <div class="fw-bold small">${p.product_name}</div>
                            <div class="text-success small">R ${parseFloat(p.price).toFixed(2)}</div>
                        </div>
                    </a>
                `).join('') + `<div class="p-2 text-center"><a href="${APP_URL}/products/index.php?search=${encodeURIComponent(query)}" class="text-primary small">View all results</a></div>`;
            } else {
                container.innerHTML = '<div class="p-3 text-muted text-center small">No products found</div>';
            }
        })
        .catch(() => {
            container.innerHTML = '';
        });
}

/**
 * Price range slider sync
 */
function initPriceRangeSlider() {
    const minInput = document.getElementById('min_price');
    const maxInput = document.getElementById('max_price');
    const minDisplay = document.getElementById('min-price-display');
    const maxDisplay = document.getElementById('max-price-display');

    if (!minInput || !maxInput) return;

    function updateDisplays() {
        if (minDisplay) minDisplay.textContent = 'R ' + (minInput.value || '0');
        if (maxDisplay) maxDisplay.textContent = maxInput.value ? 'R ' + maxInput.value : 'Any';
    }

    minInput.addEventListener('input', updateDisplays);
    maxInput.addEventListener('input', updateDisplays);
    updateDisplays();
}

/**
 * Auto submit filters on change (category, condition, sort)
 */
function initFilterAutoSubmit() {
    const autoSubmitSelects = document.querySelectorAll('.filter-auto-submit');
    autoSubmitSelects.forEach(function(select) {
        select.addEventListener('change', function() {
            this.closest('form').submit();
        });
    });
}
