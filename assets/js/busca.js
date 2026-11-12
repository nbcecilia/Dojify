document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-live-search]').forEach(function (input) {
        const target = input.dataset.searchTarget;
        const rows = target ? document.querySelectorAll(target) : [];
        const groupedRows = new Map();
        const individualRows = [];
        const container = input.closest('[data-live-search-container]');
        const status = document.createElement('p');

        rows.forEach(function (row) {
            const group = row.dataset.searchGroup;
            if (!group) {
                individualRows.push(row);
                return;
            }

            if (!groupedRows.has(group)) {
                groupedRows.set(group, []);
            }
            groupedRows.get(group).push(row);
        });

        status.className = 'text-muted small mt-2 mb-0';
        status.setAttribute('aria-live', 'polite');
        status.hidden = true;
        status.textContent = 'Nenhum resultado encontrado.';
        if (container) {
            container.appendChild(status);
        }

        function normalize(value) {
            return value.toLocaleLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim();
        }

        function filterRows() {
            const term = normalize(input.value);
            let visibleCount = 0;

            individualRows.forEach(function (row) {
                const isPlaceholder = row.classList.contains('linha-vazia') ||
                    row.hasAttribute('data-search-ignore') ||
                    row.querySelector('td[colspan]');

                if (isPlaceholder) {
                    row.hidden = term !== '';
                    return;
                }

                const text = normalize(row.dataset.searchText || row.textContent || '');
                const matches = term === '' || text.includes(term);
                row.hidden = !matches;
                if (matches) {
                    visibleCount += 1;
                }
            });

            groupedRows.forEach(function (group) {
                const matches = term === '' || group.some(function (row) {
                    return normalize(row.dataset.searchText || row.textContent || '').includes(term);
                });
                group.forEach(function (row) {
                    row.hidden = !matches;
                    if (matches) {
                        visibleCount += 1;
                    }
                });
            });

            status.hidden = term === '' || visibleCount > 0;
        }

        input.addEventListener('input', filterRows);
        filterRows();
    });
});
