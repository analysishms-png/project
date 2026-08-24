/* ============================================================================
 * Analysis HMS — Shared Report Helpers (hms-report.js)
 * ----------------------------------------------------------------------------
 * Single source of truth for fmt()/fmtDate()/radioVal() that were previously
 * copy-pasted into every report blade. Loaded from layouts/header.blade.php
 * right after jQuery + DataTables, BEFORE any page-level <script> block.
 *
 * Conventions:
 *   - Canonical names: window.hmsFmt / window.hmsFmtDate / window.hmsRadioVal
 *   - Legacy aliases:  window.fmt / window.fmtDate / window.radioVal
 *     (a blade that still defines its own local function overrides these —
 *      so including this file can never change existing behaviour)
 *
 * Part of REDIS_JS_PLAN.md Phase J-A. Additive only; no behaviour change.
 * ==========================================================================*/

(function (window, document, $) {
    'use strict';

    /**
     * Format a number using en-IN grouping with exactly 2 decimals.
     * hmsFmt(1234.5) -> "1,234.50"
     */
    function hmsFmt(v) {
        return Number(v || 0).toLocaleString('en-IN', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    /**
     * Format a date string as dd/mm/yyyy (en-GB).
     * Returns '' for empty input and the raw value when unparseable,
     * so server-provided preformatted strings pass through untouched.
     */
    function hmsFmtDate(d) {
        if (!d) return '';
        var x = new Date(d);
        if (isNaN(x)) return d;
        return x.toLocaleDateString('en-GB');
    }

    /**
     * Return the value of the checked radio input with the given name,
     * or '' when nothing is selected. jQuery-based (always loaded).
     */
    function hmsRadioVal(name) {
        var el = document.querySelector("input[name='" + name + "']:checked");
        return el ? el.value : '';
    }

    /**
     * Auto-fetch helper: re-runs fn() whenever any matching element changes
     * (radios, selects, date inputs) plus optional debounced keyup for text.
     */
    function hmsAutoFetch(bindSelector, fn) {
        $(document).on('change', bindSelector, fn);
        $(document).on('keyup', bindSelector, hmsDebounce(fn, 400));
    }

    /**
     * Trailing-edge debounce.
     */
    function hmsDebounce(fn, wait) {
        var t = null;
        return function () {
            var ctx = this, args = arguments;
            clearTimeout(t);
            t = setTimeout(function () { fn.apply(ctx, args); }, wait || 300);
        };
    }

    /**
     * Standard DataTables init used by report pages:
     * DOM export buttons (copy/excel/print), client-side paging.
     */
    function hmsTableInit(id, opts) {
        if (!$.fn.DataTable || !document.getElementById(id)) return null;
        var base = {
            dom: "<'row mb-2'<'col-sm-12 col-md-4'B><'col-sm-12 col-md-4'l><'col-sm-12 col-md-4'f>>" +
                 "<'row'<'col-sm-12'tr>><'row mt-2'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            buttons: ['copyHtml5', 'excelHtml5', 'print'],
            pageLength: 25,
            autoWidth: false
        };
        return $('#' + id).DataTable($.extend(true, {}, base, opts || {}));
    }

    // Canonical exports -----------------------------------------------------
    window.hmsFmt = hmsFmt;
    window.hmsFmtDate = hmsFmtDate;
    window.hmsRadioVal = hmsRadioVal;
    window.hmsAutoFetch = hmsAutoFetch;
    window.hmsDebounce = hmsDebounce;
    window.hmsTableInit = hmsTableInit;

    // Legacy aliases ( blades may override locally — local wins ) -----------
    if (typeof window.fmt === 'undefined') window.fmt = hmsFmt;
    if (typeof window.fmtDate === 'undefined') window.fmtDate = hmsFmtDate;
    if (typeof window.radioVal === 'undefined') window.radioVal = hmsRadioVal;
})(window, document, jQuery);
