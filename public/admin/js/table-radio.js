/**
 * table-radio.js
 * Adds radio-button row selection to any table with the class "table-radio".
 * Usage: <table class="table table-radio" id="myTable">
 *
 * When a row is selected:
 *   1. The radio is checked and the row gets .table-radio-active
 *   2. A jQuery event "table-radio:select" is triggered on the table element
 *      with data: { id: <row data-id or index>, data: <rowData> }
 *   3. Selected value is available via window.tableRadio.get(tableId)
 */
(function () {
    'use strict';

    /* ── Public API ─────────────────────────────────────────── */
    window.tableRadio = {
        _selected: {},

        /** Get { id, data, row } for a given table selector */
        get: function (tableId) {
            return this._selected[tableId] || null;
        },

        /** Clear selection for a given table */
        clear: function (tableId) {
            delete this._selected[tableId];
            var $table = $('#' + tableId);
            $table.find('.table-radio-active').removeClass('table-radio-active');
            $table.find('input.table-radio-input').prop('checked', false);
            $table.trigger('table-radio:clear');
        }
    };

    /* ── Inject radio <th> + <td> into all .table-radio tables ── */
    function initTableRadio() {
        $('.table-radio').each(function () {
            var $table = $(this);

            // Skip if already initialized
            if ($table.data('radio-init')) return;
            $table.data('radio-init', true);

            // Add header
            var $thead = $table.find('thead');
            if ($thead.length) {
                $thead.find('tr').first().prepend(
                    '<th style="width:40px;text-align:center;vertical-align:middle;">Select</th>'
                );
            }

            // Add radio to each body row
            var $tbody = $table.find('tbody');
            $tbody.find('tr').each(function (i) {
                var $row = $(this);
                $row.prepend(
                    '<td style="text-align:center;vertical-align:middle;">' +
                    '<input type="radio" name="tableRadio_' + ($table.attr('id') || 'tbl') + '" ' +
                    'class="table-radio-input" value="' + i + '">' +
                    '</td>'
                );
            });

            // Click handler — whole row is clickable
            $tbody.off('click.tableRadio').on('click.tableRadio', 'tr', function (e) {
                // Don't re-trigger if they clicked the radio itself directly
                if (!$(e.target).is('.table-radio-input')) {
                    $(this).find('.table-radio-input').prop('checked', true);
                }

                // Visual state
                $tbody.find('tr').removeClass('table-radio-active');
                $(this).addClass('table-radio-active');

                // Store + fire event
                var idx = $(this).index();
                var rowId = $(this).data('id') || idx;
                var rowData = $(this).data();

                var tableId = $table.attr('id') || 'tbl';
                window.tableRadio._selected[tableId] = {
                    id: rowId,
                    index: idx,
                    data: rowData,
                    row: $(this)
                };

                $table.trigger('table-radio:select', [{
                    id: rowId,
                    index: idx,
                    data: rowData,
                    row: $(this)
                }]);
            });
        });
    }

    /* ── Auto-init on DOM ready + on AJAX content load ─────── */
    $(function () {
        initTableRadio();

        // Re-init after DataTable draws (pagination, search, etc.)
        $(document).on('draw.dt', '.table-radio', function () {
            // DataTable resets DOM, so re-mark selected
            var $table = $(this);
            var tableId = $table.attr('id') || 'tbl';
            var sel = window.tableRadio._selected[tableId];

            $table.data('radio-init', false);
            initTableRadio();

            if (sel && typeof sel.index === 'number') {
                $table.find('tbody tr').eq(sel.index)
                    .addClass('table-radio-active')
                    .find('.table-radio-input').prop('checked', true);
            }
        });

        // MutationObserver fallback for dynamically injected tables
        if (typeof MutationObserver !== 'undefined') {
            var obs = new MutationObserver(function (mutations) {
                var needsInit = false;
                mutations.forEach(function (m) {
                    m.addedNodes.forEach(function (n) {
                        if (n.nodeType === 1 && (
                            $(n).hasClass('table-radio') ||
                            $(n).find('.table-radio').length
                        )) {
                            needsInit = true;
                        }
                    });
                });
                if (needsInit) initTableRadio();
            });
            obs.observe(document.body, { childList: true, subtree: true });
        }
    });
})();
