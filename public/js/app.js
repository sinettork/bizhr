document.addEventListener('DOMContentLoaded', () => {
    const enhanceLiveSearch = (root = document) => {
        if (! window.htmx) return;

        root.querySelectorAll('.workspace-command-filters .reference-filter-form').forEach((form) => {
            const search = form.querySelector('input[name="search"]');
            const workspace = form.closest('.app-workspace');
            const results = workspace?.querySelector('.reference-list[data-list-container]');
            if (! search || ! results) return;

            if (! form.dataset.liveSearchReady) {
                form.dataset.liveSearchReady = 'true';
                const fallbackSubmit = form.querySelector('.reference-search-button, button[type="submit"], button:not([type])');
                fallbackSubmit?.classList.add('live-search-submit-fallback');
                form.setAttribute('hx-get', form.getAttribute('action') || window.location.pathname);
                form.setAttribute('hx-target', '.app-workspace .reference-list[data-list-container]');
                form.setAttribute('hx-select', '.reference-list[data-list-container]');
                form.setAttribute('hx-swap', 'outerHTML');
                form.setAttribute('hx-push-url', 'true');
                form.setAttribute('hx-sync', 'this:replace');
                form.setAttribute('hx-indicator', '.live-search-indicator');
                form.setAttribute('hx-trigger', 'submit, input changed delay:350ms from:input[name="search"], change from:select');

                const indicator = document.createElement('span');
                indicator.className = 'live-search-indicator htmx-indicator';
                indicator.setAttribute('role', 'status');
                indicator.setAttribute('aria-live', 'polite');
                indicator.innerHTML = '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span><span>Searching...</span>';
                form.append(indicator);
                window.htmx.process(form);
            }

            results.setAttribute('hx-boost', 'true');
            results.setAttribute('hx-target', '.app-workspace .reference-list[data-list-container]');
            results.setAttribute('hx-select', '.reference-list[data-list-container]');
            results.setAttribute('hx-swap', 'outerHTML');
            results.setAttribute('hx-push-url', 'true');
            results.querySelectorAll('.pagination-size-form').forEach((sizeForm) => {
                sizeForm.setAttribute('hx-get', window.location.pathname);
                sizeForm.setAttribute('hx-target', '.app-workspace .reference-list[data-list-container]');
                sizeForm.setAttribute('hx-select', '.reference-list[data-list-container]');
                sizeForm.setAttribute('hx-swap', 'outerHTML');
                sizeForm.setAttribute('hx-push-url', 'true');
                sizeForm.setAttribute('hx-trigger', 'change');
                sizeForm.querySelector('select')?.removeAttribute('onchange');
            });
            window.htmx.process(results);
        });
    };

    enhanceLiveSearch();
    document.body.addEventListener('htmx:afterSwap', (event) => enhanceLiveSearch(event.detail.target.closest('.app-workspace') || document));
    document.body.addEventListener('htmx:beforeRequest', (event) => {
        event.detail.target?.setAttribute('aria-busy', 'true');
    });
    document.body.addEventListener('htmx:afterRequest', (event) => {
        event.detail.target?.removeAttribute('aria-busy');
    });
    document.body.addEventListener('htmx:responseError', () => {
        const workspace = document.querySelector('.app-workspace');
        if (! workspace || workspace.querySelector('[data-live-search-error]')) return;
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger live-search-error';
        alert.dataset.liveSearchError = 'true';
        alert.setAttribute('role', 'alert');
        alert.textContent = 'Search could not be refreshed. Your current results were kept; please try again.';
        workspace.prepend(alert);
        window.setTimeout(() => alert.remove(), 6000);
    });

    document.querySelectorAll('.reference-list').forEach((list) => {
        const table = list.querySelector(':scope > .table-responsive > table');
        if (!table || list.querySelector(':scope > .pagination-footer, :scope > .reference-list-footer')) return;

        const rows = table.querySelectorAll('tbody > tr').length;
        const params = new URLSearchParams(window.location.search);
        const perPage = params.get('per_page') || '20';
        const footer = document.createElement('div');
        footer.className = 'reference-list-footer pagination-footer';
        footer.setAttribute('aria-label', 'Table pagination');
        footer.innerHTML = `<div class="pagination-navigation"></div><div class="pagination-controls"><span class="pagination-summary">Showing ${rows ? 1 : 0}-${rows} of ${rows} &middot; Page 1 of 1</span><label class="visually-hidden" for="fallback-per-page">Rows per page</label><select id="fallback-per-page" class="form-select form-select-sm" aria-label="Rows per page">${[10,20,30,50,100].map(size => `<option value="${size}" ${String(size) === perPage ? 'selected' : ''}>${size}</option>`).join('')}</select><span>rows</span></div>`;
        footer.querySelector('select')?.addEventListener('change', (event) => {
            params.set('per_page', event.target.value);
            params.delete('page');
            window.location.search = params.toString();
        });
        list.appendChild(footer);
    });

    document.querySelectorAll('.workspace-command-bar').forEach((commandBar) => {
        if (commandBar.getAttribute('aria-label') === 'Review queue workspace') return;
        const workspace = commandBar.parentElement;
        workspace?.querySelectorAll(':scope > .reference-list > .reference-list-toolbar').forEach((toolbar) => toolbar.remove());
    });

    // Keep all modal CRUD forms consistent: every footer gets a non-destructive
    // exit action, including older forms that were created without one.
    document.querySelectorAll('.modal .modal-footer').forEach((footer) => {
        if (footer.querySelector('[data-bs-dismiss="modal"]')) return;

        const cancel = document.createElement('button');
        cancel.type = 'button';
        cancel.className = 'btn btn-light';
        cancel.dataset.bsDismiss = 'modal';
        cancel.textContent = 'Cancel';
        footer.prepend(cancel);
    });

    document.querySelectorAll('[data-auto-dismiss]').forEach((element) => {
        window.setTimeout(() => element.remove(), 5000);
    });

    document.querySelectorAll('input[type="file"][data-image-preview]').forEach((input) => {
        input.addEventListener('change', () => {
            const preview = document.getElementById(input.dataset.imagePreview);
            const file = input.files?.[0];
            if (! preview || ! file || ! file.type.startsWith('image/')) return;
            preview.src = URL.createObjectURL(file);
            preview.hidden = false;
            preview.closest('.image-upload-zone')?.querySelector('.image-upload-placeholder')?.setAttribute('hidden', '');
        });
    });

    document.querySelectorAll('.reference-list').forEach((list) => {
        const existingFooter = list.querySelector('.reference-list-footer');
        if (existingFooter) {
            if (! existingFooter.classList.contains('pagination-footer')) {
                existingFooter.classList.add('pagination-footer');
                const navigation = document.createElement('div');
                navigation.className = 'pagination-navigation';
                while (existingFooter.firstChild) navigation.append(existingFooter.firstChild);
                existingFooter.append(navigation);
                const controls = document.createElement('div');
                controls.className = 'pagination-controls';
                const sizes = [10, 20, 30, 50, 100];
                const selected = Number(new URLSearchParams(location.search).get('per_page')) || 20;
                const pagination = navigation.querySelector('[data-pagination-total]');
                const summary = pagination ? `Showing ${pagination.dataset.paginationFirst}–${pagination.dataset.paginationLast} of ${pagination.dataset.paginationTotal} · Page ${pagination.dataset.paginationCurrent} of ${pagination.dataset.paginationPages}` : '';
                controls.innerHTML = `${summary ? `<span class="pagination-summary">${summary}</span>` : ''}<label class="pagination-size-form"><span class="visually-hidden">Rows per page</span><select class="form-select form-select-sm" aria-label="Rows per page">${sizes.map((size) => `<option value="${size}" ${size === selected ? 'selected' : ''}>${size}</option>`).join('')}</select><span>rows</span></label>`;
                controls.querySelector('select').addEventListener('change', (event) => {
                    const url = new URL(location.href);
                    url.searchParams.set('per_page', event.currentTarget.value);
                    url.searchParams.delete('page');
                    location.assign(url);
                });
                existingFooter.append(controls);
            }
            return;
        }
        const rows = [...list.querySelectorAll('tbody > tr')].filter((row) => ! row.querySelector('td[colspan]'));
        if (! rows.length) return;
        const footer = document.createElement('div');
        footer.className = 'reference-list-footer pagination-footer';
        footer.setAttribute('aria-label', 'Table pagination');
        const sizes = [10, 20, 30, 50, 100];
        const selected = Number(new URLSearchParams(location.search).get('per_page')) || 20;
        footer.innerHTML = `<div class="pagination-navigation"></div><div class="pagination-controls"><span class="pagination-summary">Showing 1–${rows.length} of ${rows.length} · Page 1 of 1</span><label class="pagination-size-form"><span class="visually-hidden">Rows per page</span><select class="form-select form-select-sm" aria-label="Rows per page">${sizes.map((size) => `<option value="${size}" ${size === selected ? 'selected' : ''}>${size}</option>`).join('')}</select><span>rows</span></label></div>`;
        footer.querySelector('select')?.addEventListener('change', (event) => {
            const url = new URL(location.href);
            url.searchParams.set('per_page', event.currentTarget.value);
            url.searchParams.delete('page');
            location.assign(url);
        });
        list.appendChild(footer);
    });

    const dashboardGrid = document.querySelector('[data-dashboard-grid]');
    if (dashboardGrid) {
        const preferenceNode = document.getElementById('dashboard-preferences');
        const preferences = JSON.parse(preferenceNode?.textContent || '{}');
        const widgets = () => [...dashboardGrid.querySelectorAll('[data-widget]')];
        const editActions = document.querySelector('.dashboard-edit-actions');
        const viewActions = document.querySelector('.dashboard-view-actions');
        let snapshot = null;
        let dragged = null;

        const state = () => ({
            order: widgets().map((widget) => widget.dataset.widget),
            hidden: widgets().filter((widget) => widget.hidden).map((widget) => widget.dataset.widget),
        });
        const applyState = ({ order = [], hidden = [] }) => {
            const byKey = new Map(widgets().map((widget) => [widget.dataset.widget, widget]));
            order.forEach((key) => { if (byKey.has(key)) dashboardGrid.append(byKey.get(key)); });
            [...byKey.values()].forEach((widget) => { if (! order.includes(widget.dataset.widget)) dashboardGrid.append(widget); });
            widgets().forEach((widget) => { widget.hidden = hidden.includes(widget.dataset.widget); });
            document.querySelectorAll('[data-dashboard-widget-toggle]').forEach((toggle) => { toggle.checked = ! hidden.includes(toggle.value); });
        };
        const defaultState = () => ({
            order: widgets().sort((a, b) => Number(a.dataset.defaultOrder) - Number(b.dataset.defaultOrder)).map((widget) => widget.dataset.widget),
            hidden: [],
        });
        const setEditing = (editing) => {
            dashboardGrid.classList.toggle('is-editing', editing);
            editActions.hidden = ! editing;
            viewActions.hidden = editing;
            widgets().forEach((widget) => {
                widget.draggable = editing;
                widget.querySelector('[data-widget-remove]')?.toggleAttribute('hidden', ! editing);
                widget.querySelector('.dashboard-widget-handle')?.toggleAttribute('hidden', ! editing);
            });
        };
        const persist = async (url, method, body = null) => {
            const response = await fetch(url, {
                method,
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
                body: body ? JSON.stringify(body) : null,
            });
            if (! response.ok) throw new Error('Dashboard preferences could not be saved.');
        };

        applyState({ order: preferences.order || [], hidden: preferences.hidden || [] });
        document.querySelector('[data-dashboard-edit]')?.addEventListener('click', () => { snapshot = state(); setEditing(true); });
        document.querySelector('[data-dashboard-cancel]')?.addEventListener('click', () => { if (snapshot) applyState(snapshot); setEditing(false); });
        document.querySelector('[data-dashboard-auto]')?.addEventListener('click', () => applyState(defaultState()));
        document.querySelector('[data-dashboard-save]')?.addEventListener('click', async (event) => {
            const button = event.currentTarget;
            button.disabled = true;
            try { await persist(window.dashboardPreferenceUrl, 'PUT', state()); snapshot = state(); setEditing(false); }
            catch (error) { window.alert(error.message); }
            finally { button.disabled = false; }
        });
        document.querySelector('[data-dashboard-reset]')?.addEventListener('click', async (event) => {
            const button = event.currentTarget;
            button.disabled = true;
            try { await persist(window.dashboardResetUrl, 'DELETE'); applyState(defaultState()); snapshot = state(); setEditing(false); }
            catch (error) { window.alert(error.message); }
            finally { button.disabled = false; }
        });
        document.querySelectorAll('[data-dashboard-widget-toggle]').forEach((toggle) => toggle.addEventListener('change', () => {
            const widget = dashboardGrid.querySelector(`[data-widget="${CSS.escape(toggle.value)}"]`);
            if (widget) widget.hidden = ! toggle.checked;
        }));
        dashboardGrid.addEventListener('click', (event) => {
            const remove = event.target.closest('[data-widget-remove]');
            if (! remove) return;
            const widget = remove.closest('[data-widget]');
            widget.hidden = true;
            const toggle = document.querySelector(`[data-dashboard-widget-toggle][value="${CSS.escape(widget.dataset.widget)}"]`);
            if (toggle) toggle.checked = false;
        });
        dashboardGrid.addEventListener('dragstart', (event) => {
            dragged = event.target.closest('[data-widget]');
            if (! dragged || ! dashboardGrid.classList.contains('is-editing')) return event.preventDefault();
            dragged.classList.add('is-dragging');
            event.dataTransfer.effectAllowed = 'move';
        });
        dashboardGrid.addEventListener('dragover', (event) => {
            event.preventDefault();
            const target = event.target.closest('[data-widget]');
            if (! dragged || ! target || target === dragged) return;
            const box = target.getBoundingClientRect();
            const draggedBox = dragged.getBoundingClientRect();
            const sameRow = Math.abs(draggedBox.top - box.top) < Math.min(draggedBox.height, box.height) / 2;
            const insertBefore = sameRow
                ? event.clientX < box.left + box.width / 2
                : event.clientY < box.top + box.height / 2;
            dashboardGrid.insertBefore(dragged, insertBefore ? target : target.nextSibling);
        });
        dashboardGrid.addEventListener('dragend', () => { dragged?.classList.remove('is-dragging'); dragged = null; });
    }

    // Print button handler
    document.querySelectorAll('[data-action="print"]').forEach((button) => {
        button.addEventListener('click', () => window.print());
    });

    document.querySelectorAll('[data-list-actions]').forEach((toolbar) => {
        const container = toolbar.closest('[data-list-container]') || document.querySelector('.reference-list[data-list-container]');
        const isEmployeesPage = window.location.pathname.includes('/employees');
        const exportBtn = toolbar.querySelector('[data-list-export]');
        
        // Employees export only becomes a dropdown after at least one row is selected.
        if (isEmployeesPage && exportBtn) {
            exportBtn.removeAttribute('data-list-export');
            exportBtn.disabled = true;
            exportBtn.setAttribute('aria-disabled', 'true');
            exportBtn.classList.remove('dropdown-toggle');
            exportBtn.removeAttribute('data-bs-toggle');
            exportBtn.removeAttribute('aria-expanded');
            
            const dropdownMenu = document.getElementById('employeeExportMenu');
            if (dropdownMenu) {
                exportBtn.parentElement.insertBefore(dropdownMenu, exportBtn.nextSibling);
                dropdownMenu.style.position = 'absolute';

                const syncExportButtonState = () => {
                    const table = container?.querySelector('table') || document.querySelector('.reference-list[data-list-container] table');
                    const selectedCount = table ? table.querySelectorAll('input[type="checkbox"].employee-row-select:checked').length : 0;
                    const hasSelection = selectedCount > 0;

                    exportBtn.disabled = !hasSelection;
                    exportBtn.setAttribute('aria-disabled', String(!hasSelection));

                    if (hasSelection) {
                        exportBtn.classList.add('dropdown-toggle');
                        exportBtn.setAttribute('data-bs-toggle', 'dropdown');
                        exportBtn.setAttribute('aria-expanded', 'false');
                    } else {
                        exportBtn.classList.remove('dropdown-toggle');
                        exportBtn.removeAttribute('data-bs-toggle');
                        exportBtn.removeAttribute('aria-expanded');
                        bootstrap.Dropdown.getInstance(exportBtn)?.hide();
                    }
                };

                exportBtn.addEventListener('click', (event) => {
                    const table = container?.querySelector('table') || document.querySelector('.reference-list[data-list-container] table');
                    const selectedCheckboxes = table?.querySelectorAll('input[type="checkbox"].employee-row-select:checked');
                    if (!selectedCheckboxes || selectedCheckboxes.length === 0) {
                        event.preventDefault();
                        event.stopPropagation();
                        return;
                    }

                    if (!exportBtn.hasAttribute('data-bs-toggle')) {
                        event.preventDefault();
                        event.stopPropagation();
                        return;
                    }
                });

                dropdownMenu.querySelectorAll('[data-export-type]').forEach((btn) => {
                    btn.addEventListener('click', (e) => {
                        e.preventDefault();
                        const currentTable = container?.querySelector('table') || document.querySelector('.reference-list[data-list-container] table');
                        handleExport(btn.dataset.exportType, { querySelector: () => currentTable });
                        bootstrap.Dropdown.getInstance(exportBtn)?.hide();
                    });
                });

                document.querySelectorAll('.employee-row-select, .employee-select-all').forEach((checkbox) => {
                    checkbox.addEventListener('change', syncExportButtonState);
                });

                syncExportButtonState();
            }
        }
        
        toolbar.querySelector('[data-list-print]')?.addEventListener('click', () => window.print());
        toolbar.querySelector('[data-list-compact]')?.addEventListener('click', (event) => {
            const compact = container?.classList.toggle('compact-view');
            event.currentTarget.setAttribute('aria-pressed', compact ? 'true' : 'false');
            event.currentTarget.classList.toggle('active', compact);
        });
        
        // Standard export for non-employees pages
        if (!isEmployeesPage) {
            toolbar.querySelector('[data-list-export]')?.addEventListener('click', () => {
                const table = container?.querySelector('table');
                if (! table) return;
                const rows = [...table.querySelectorAll('tr')].map((row) => [...row.querySelectorAll('th, td')].map((cell) => `"${cell.innerText.trim().replaceAll('"', '""')}"`).join(','));
                const file = new Blob([rows.join('\n')], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(file);
                link.download = `${document.title.replaceAll(/[^a-z0-9]+/gi, '-').toLowerCase()}.csv`;
                link.click();
                URL.revokeObjectURL(link.href);
            });
        }
    });

    // Export handler function
    function handleExport(exportType, container) {
        const table = container?.querySelector('table');
        if (!table) return;

        let rows = [];
        
        if (exportType === 'selected') {
            // Export only selected rows
            const selectedCheckboxes = table.querySelectorAll('input[type="checkbox"].employee-row-select:checked');
            if (selectedCheckboxes.length === 0) {
                alert('Please select at least one employee to export.');
                return;
            }
            
            // Get header row
            const headers = [...table.querySelectorAll('thead th')].map((th) => `"${th.innerText.trim().replaceAll('"', '""')}"`);
            rows.push(headers.join(','));
            
            // Get selected rows
            selectedCheckboxes.forEach((checkbox) => {
                const row = checkbox.closest('tr');
                if (row) {
                    const cells = [...row.querySelectorAll('td')].map((td) => `"${td.innerText.trim().replaceAll('"', '""')}"`);
                    rows.push(cells.join(','));
                }
            });
        } else {
            // Export all rows
            rows = [...table.querySelectorAll('tr')].map((row) => [...row.querySelectorAll('th, td')].map((cell) => `"${cell.innerText.trim().replaceAll('"', '""')}"`).join(','));
        }
        
        // Download
        const file = new Blob([rows.join('\n')], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(file);
        link.download = `employees-${exportType === 'selected' ? 'selected-' : ''}${new Date().toISOString().split('T')[0]}.csv`;
        link.click();
        URL.revokeObjectURL(link.href);
    }

    // Column choices are opt-in. They belong on dense, reusable data tables—not
    // on every operational list or small configuration table.
    document.querySelectorAll('.table-responsive > table').forEach((table, tableIndex) => {
        const headers = [...table.querySelectorAll('thead th')];
        if (headers.length < 2) return;
        const surface = table.closest('.reference-list, .card') || table.closest('.table-responsive');
        const toolbar = surface?.querySelector('[data-list-actions]');
        const columnSlot = toolbar?.querySelector('[data-column-chooser]');
        if (!columnSlot) return;
        surface?.classList.add('employee-pattern-list');
        surface?.setAttribute('data-list-container', '');
        const tableKey = `${location.pathname.replaceAll('/', ':') || ':home'}:${tableIndex}`.replaceAll(/[^a-z0-9:._-]/gi, '-');
        const preferences = window.appTablePreferences || {};
        const hidden = new Set(preferences[tableKey] || []);
        const apply = () => {
            [...table.querySelectorAll('tr')].forEach((row) => [...row.children].forEach((cell, index) => {
                cell.hidden = hidden.has(index);
            }));
        };
        apply();
        const menu = document.createElement('div');
        menu.className = 'dropdown table-column-chooser';
        menu.innerHTML = `<button class="btn btn-action-link btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="fa-solid fa-table-columns"></i><span>Columns</span></button><div class="dropdown-menu dropdown-menu-end p-2 shadow" aria-label="Choose table columns"></div>`;
        const dropdown = menu.querySelector('.dropdown-menu');
        headers.forEach((header, index) => {
            const label = header.textContent.trim() || `Column ${index + 1}`;
            if (!label || /^(actions?|decision)$/i.test(label) || (headers.length === 2 && index === 1)) return;
            const item = document.createElement('label');
            item.className = 'dropdown-item-text form-check small py-1';
            item.innerHTML = `<input class="form-check-input me-2" type="checkbox" ${hidden.has(index) ? '' : 'checked'}><span></span>`;
            item.querySelector('span').textContent = label;
            item.querySelector('input').addEventListener('change', (event) => {
                if (event.currentTarget.checked) hidden.delete(index); else hidden.add(index);
                apply();
                const body = JSON.stringify({ table: tableKey, hidden_columns: [...hidden] });
                fetch(window.tablePreferenceUrl, { method: 'PUT', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content }, body }).catch(() => {});
            });
            dropdown.append(item);
        });
        columnSlot.replaceWith(menu);
    });
});