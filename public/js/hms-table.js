/**
 * hms-table.js — auto-enhance heavy <table> elements with DataTables.
 *
 * Any <table> whose tbody has ≥ `data-table-rows` (default 100) rows is
 * automatically initialised with jQuery DataTables unless:
 *   - it is already a DataTable  (`.dataTable` class present)
 *   - it is wrapped inside an existing `.dataTables_wrapper` container
 *   - it opts out with `data-table-no="true"` attribute
 *
 * Per-table overrides via JSON in `data-table-options`:
 *   <table data-table-rows="25" data-table-options='{"pageLength":25,"lengthMenu":[[25,50,-1],["25","50","All"]]}'>
 *
 * Exposed API: window.hmsEnhanceHeavyTables(opts?)
 *   opts.threshold — global row-count minimum (default 100)
 *   opts.init      — boolean false to just query count without initing
 */

(function () {
    'use strict';

    if (typeof jQuery === 'undefined') return;       // DataTables not available
    var $ = jQuery;

    var DEFAULTS = {
        threshold: 100,
        pageLength: 25,
        lengthMenu: [[25, 50, 100, -1], ['25', '50', '100', 'All']],
        language:  { search: '_INPUT_', searchPlaceholder: 'Search…' },
        dom: 'lfrtip',
        order: []
    };

    function enhance(opts) {
        opts = $.extend({}, DEFAULTS, opts || {});

        $('table').each(function () {
            var $t = $(this);

            // Skip already initialised / DataTables-wrapped / opted-out tables
            if ($t.closest('.dataTables_wrapper').length) return;
            if ($t.hasClass('dataTable') || $t.hasClass('dt-responsive')) return;
            if ($t.attr('data-table-no') === 'true') return;

            var minRows = parseInt($t.attr('data-table-rows'), 10) || opts.threshold;
            var rowCount = $t.find('tbody tr').length;
            if (rowCount < minRows) return;

            var overrides = {};
            try {
                overrides = JSON.parse($t.attr('data-table-options') || '{}');
            } catch (_) { /* ignore malformed JSON */ }

            $t.DataTable($.extend(true, {}, opts, overrides));
        });
    }

    // Expose for manual / late calls
    window.hmsEnhanceHeavyTables = enhance;

    // Run once DOM is ready
    $(function () { enhance(); });
})();
