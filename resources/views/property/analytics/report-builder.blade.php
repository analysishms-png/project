@extends('property.layouts.property')

@section('content')

<style>
    .rb-card {
        background: #fff;
        border: 1px solid #e8ecf1;
        border-radius: 14px;
        padding: 24px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }
    .rb-card h6 { font-weight: 700; color: #1e293b; margin-bottom: 16px; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; }
    .source-btn {
        display: inline-block;
        padding: 8px 16px;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        margin: 4px;
        cursor: pointer;
        font-size: 13px;
        font-weight: 600;
        transition: all 0.2s;
        background: #fff;
    }
    .source-btn:hover, .source-btn.active {
        border-color: #667eea;
        background: rgba(102,126,234,0.08);
        color: #667eea;
    }
    .col-check {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        margin: 3px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: 12px;
        cursor: pointer;
        background: #fff;
        transition: all 0.15s;
    }
    .col-check:hover { background: #f8fafc; border-color: #cbd5e1; }
    .col-check input:checked + span { color: #667eea; font-weight: 600; }
    .filter-row {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
    }
    .filter-row select, .filter-row input {
        border: 1px solid #d1d5db;
        border-radius: 8px;
        padding: 7px 12px;
        font-size: 13px;
    }
    .rb-table {
        font-size: 12px;
        border-collapse: collapse;
        width: 100%;
    }
    .rb-table th {
        background: #f1f5f9;
        padding: 10px 12px;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 10px;
        letter-spacing: 0.5px;
        color: #475569;
        border-bottom: 2px solid #e2e8f0;
        white-space: nowrap;
        cursor: pointer;
    }
    .rb-table th:hover { background: #e2e8f0; }
    .rb-table td { padding: 8px 12px; border-bottom: 1px solid #f1f5f9; }
    .rb-table tr:hover td { background: #f8fafc; }
    .config-badge {
        display: inline-block;
        background: #ede9fe;
        color: #7c3aed;
        padding: 2px 8px;
        border-radius: 6px;
        font-size: 11px;
        margin: 2px;
    }
    .btn-generate {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        border: none;
        padding: 10px 32px;
        border-radius: 10px;
        font-weight: 700;
        cursor: pointer;
        font-size: 14px;
        transition: transform 0.2s;
    }
    .btn-generate:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(102,126,234,0.4); }
    .result-info { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; padding: 12px 20px; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center; }
</style>

<div class="nk-block">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 style="font-weight:800;color:#1e293b;margin:0;">
                <i class="fa-solid fa-wand-magic-sparkles" style="color:#667eea;margin-right:8px;"></i>
                Custom Report Builder
            </h4>
            <small style="color:#94a3b8;">Select data source, choose columns, apply filters, and generate</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('analytics.bi-dashboard') }}" class="btn btn-sm btn-outline-primary" style="border-radius:8px;">
                <i class="fa-solid fa-chart-line"></i> BI Dashboard
            </a>
            <a href="{{ route('analytics.saved-reports') }}" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;">
                <i class="fa-solid fa-bookmark"></i> Saved Reports
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('analytics.report-builder') }}" id="reportForm">
        @csrf

        {{-- ═══ DATA SOURCE SELECTION ═══ --}}
        <div class="rb-card">
            <h6><i class="fa-solid fa-database" style="color:#667eea;margin-right:6px;"></i> 1. Select Data Source</h6>
            @php $selectedSource = $reportConfig['dataSource'] ?? ''; @endphp
            @foreach($dataSources as $key => $src)
                <label class="source-btn {{ $selectedSource === $key ? 'active' : '' }}">
                    <input type="radio" name="datasource" value="{{ $key }}" {{ $selectedSource === $key ? 'checked' : '' }}
                           onchange="loadColumns('{{ $key }}')" style="display:none;">
                    {{ $src['label'] }}
                </label>
            @endforeach
        </div>

        {{-- ═══ COLUMN SELECTION ═══ --}}
        <div class="rb-card" id="columnsCard" style="{{ $selectedSource ? '' : 'opacity:0.5;pointer-events:none;' }}">
            <h6><i class="fa-solid fa-table-columns" style="color:#f59e0b;margin-right:6px;"></i> 2. Select Columns</h6>
            <div id="columnsList">
                @if($selectedSource && isset($dataSources[$selectedSource]))
                    @foreach($dataSources[$selectedSource]['columns'] as $col)
                        <label class="col-check">
                            <input type="checkbox" name="columns[]" value="{{ $col }}"
                                {{ in_array($col, $reportConfig['columns'] ?? []) ? 'checked' : '' }}>
                            <span>{{ $col }}</span>
                        </label>
                    @endforeach
                @endif
            </div>
        </div>

        {{-- ═══ FILTERS ═══ --}}
        <div class="rb-card" id="filtersCard" style="{{ $selectedSource ? '' : 'opacity:0.5;pointer-events:none;' }}">
            <h6><i class="fa-solid fa-filter" style="color:#10b981;margin-right:6px;"></i> 3. Apply Filters</h6>
            <div id="filtersList">
                @if($selectedSource && isset($dataSources[$selectedSource]))
                    @foreach($dataSources[$selectedSource]['filters'] as $f)
                        <div class="filter-row">
                            <select name="filters[]" style="width:180px;">
                                <option value="{{ $f }}">{{ $f }}</option>
                            </select>
                            <input type="text" name="filter_values[{{ $f }}]" placeholder="Value..."
                                   value="{{ $reportConfig['filterValues'][$f] ?? '' }}" style="width:220px;">
                            @if(in_array($f, ['vdate', 'chkindate', 'depdate']))
                                <span class="text-muted">to</span>
                                <input type="date" name="filter_values[{{ $f }}_to]"
                                       value="{{ $reportConfig['filterValues'][$f.'_to'] ?? '' }}">
                            @endif
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        {{-- ═══ OPTIONS ═══ --}}
        <div class="rb-card" id="optionsCard" style="{{ $selectedSource ? '' : 'opacity:0.5;pointer-events:none;' }}">
            <h6><i class="fa-solid fa-sliders" style="color:#8b5cf6;margin-right:6px;"></i> 4. Options</h6>
            <div class="d-flex gap-3 flex-wrap">
                <div>
                    <label class="form-label" style="font-size:12px;font-weight:600;">Group By</label>
                    <select name="groupby" class="form-control form-control-sm" style="border-radius:8px;width:180px;">
                        <option value="">— None —</option>
                        @if($selectedSource && isset($dataSources[$selectedSource]))
                            @foreach($dataSources[$selectedSource]['columns'] as $col)
                                <option value="{{ $col }}" {{ ($reportConfig['groupBy'] ?? '') === $col ? 'selected' : '' }}>{{ $col }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div>
                    <label class="form-label" style="font-size:12px;font-weight:600;">Order By</label>
                    <select name="orderby" class="form-control form-control-sm" style="border-radius:8px;width:180px;">
                        @if($selectedSource && isset($dataSources[$selectedSource]))
                            @foreach($dataSources[$selectedSource]['columns'] as $col)
                                <option value="{{ $col }}" {{ ($reportConfig['orderBy'] ?? 'vdate') === $col ? 'selected' : '' }}>{{ $col }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div>
                    <label class="form-label" style="font-size:12px;font-weight:600;">Direction</label>
                    <select name="orderdir" class="form-control form-control-sm" style="border-radius:8px;width:100px;">
                        <option value="DESC" {{ ($reportConfig['orderDir'] ?? 'DESC') === 'DESC' ? 'selected' : '' }}>DESC</option>
                        <option value="ASC" {{ ($reportConfig['orderDir'] ?? '') === 'ASC' ? 'selected' : '' }}>ASC</option>
                    </select>
                </div>
                <div>
                    <label class="form-label" style="font-size:12px;font-weight:600;">Limit</label>
                    <input type="number" name="limit" value="{{ $reportConfig['limit'] ?? 100 }}" min="10" max="10000"
                           class="form-control form-control-sm" style="border-radius:8px;width:100px;">
                </div>
            </div>
        </div>

        {{-- ═══ ACTION BAR ═══ --}}
        <div class="d-flex gap-3 mb-4">
            <button type="submit" class="btn-generate">
                <i class="fa-solid fa-play"></i> Generate Report
            </button>
            <button type="button" class="btn btn-outline-primary" style="border-radius:10px;" onclick="saveCurrentReport()">
                <i class="fa-solid fa-floppy-disk"></i> Save Report
            </button>
        </div>
    </form>

    {{-- ═══ RESULTS ═══ --}}
    @if($reportData)
        <div class="rb-card">
            <div class="result-info">
                <div>
                    <strong style="color:#10b981;">
                        <i class="fa-solid fa-check-circle"></i> {{ $reportData->count() }} records found
                    </strong>
                    @if($reportConfig)
                        <span class="text-muted" style="font-size:12px;margin-left:8px;">
                            Source: {{ $dataSources[$reportConfig['dataSource']]['label'] }}
                        </span>
                    @endif
                </div>
                <div class="d-flex gap-2">
                    <button onclick="exportCSV()" class="btn btn-sm btn-success" style="border-radius:8px;">
                        <i class="fa-solid fa-file-csv"></i> CSV
                    </button>
                    <button onclick="window.print()" class="btn btn-sm btn-secondary" style="border-radius:8px;">
                        <i class="fa-solid fa-print"></i> Print
                    </button>
                </div>
            </div>

            <div style="overflow-x:auto;">
                <table class="rb-table" id="resultTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            @foreach($reportConfig['columns'] as $col)
                                <th onclick="sortTable({{ $loop->index }})">{{ $col }} ↕</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reportData as $row)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                @foreach($reportConfig['columns'] as $col)
                                    <td>{{ $row->$col ?? '—' }}</td>
                                @endforeach
                            </tr>
                        @empty
                            <tr><td colspan="{{ count($reportConfig['columns']) + 1 }}" class="text-center text-muted" style="padding:40px;">No records found for the selected criteria</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- ═══ SAVE REPORT MODAL ═══ --}}
    <div class="modal fade" id="saveReportModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius:16px;">
                <div class="modal-header" style="border-bottom:1px solid #f1f5f9;">
                    <h6 class="modal-title" style="font-weight:700;">Save Custom Report</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" style="font-weight:600;font-size:13px;">Report Name</label>
                        <input type="text" id="reportName" class="form-control" placeholder="e.g. Daily Revenue Summary" style="border-radius:10px;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-weight:600;font-size:13px;">Description (optional)</label>
                        <textarea id="reportDesc" class="form-control" rows="2" placeholder="Brief description..." style="border-radius:10px;"></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid #f1f5f9;">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary btn-sm" onclick="submitSaveReport()" style="background:linear-gradient(135deg,#667eea,#764ba2);border:none;">
                        <i class="fa-solid fa-floppy-disk"></i> Save
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
var dataSources = @json($dataSources);

function loadColumns(key) {
    var src = dataSources[key];
    if (!src) return;

    document.getElementById('columnsCard').style.opacity = '1';
    document.getElementById('columnsCard').style.pointerEvents = 'auto';
    document.getElementById('filtersCard').style.opacity = '1';
    document.getElementById('filtersCard').style.pointerEvents = 'auto';
    document.getElementById('optionsCard').style.opacity = '1';
    document.getElementById('optionsCard').style.pointerEvents = 'auto';

    // Columns
    var html = '';
    src.columns.forEach(function(c) {
        html += '<label class="col-check"><input type="checkbox" name="columns[]" value="' + c + '" checked><span>' + c + '</span></label>';
    });
    document.getElementById('columnsList').innerHTML = html;

    // Filters
    html = '';
    src.filters.forEach(function(f) {
        var isDate = ['vdate','chkindate','depdate'].includes(f);
        html += '<div class="filter-row">';
        html += '<select name="filters[]" style="width:180px;"><option value="' + f + '">' + f + '</option></select>';
        html += '<input type="text" name="filter_values[' + f + ']" placeholder="Value..." style="width:220px;">';
        if (isDate) {
            html += '<span class="text-muted">to</span>';
            html += '<input type="date" name="filter_values[' + f + '_to]">';
        }
        html += '</div>';
    });
    document.getElementById('filtersList').innerHTML = html;

    // Update group by / order by
    var groupHtml = '<option value="">— None —</option>';
    var orderHtml = '';
    src.columns.forEach(function(c) {
        groupHtml += '<option value="' + c + '">' + c + '</option>';
        orderHtml += '<option value="' + c + '">' + c + '</option>';
    });
    document.querySelector('[name="groupby"]').innerHTML = groupHtml;
    document.querySelector('[name="orderby"]').innerHTML = orderHtml;

    // Active state
    document.querySelectorAll('.source-btn').forEach(function(b) { b.classList.remove('active'); });
    event.target.closest('.source-btn').classList.add('active');
}

function sortTable(colIndex) {
    var table = document.getElementById('resultTable');
    var rows = Array.from(table.tBodies[0].rows);
    var dir = table.dataset.sortDir === 'asc' ? 'desc' : 'asc';
    table.dataset.sortDir = dir;

    rows.sort(function(a, b) {
        var aVal = a.cells[colIndex + 1].textContent.trim();
        var bVal = b.cells[colIndex + 1].textContent.trim();
        var aNum = parseFloat(aVal.replace(/[₹,]/g, ''));
        var bNum = parseFloat(bVal.replace(/[₹,]/g, ''));
        if (!isNaN(aNum) && !isNaN(bNum)) {
            return dir === 'asc' ? aNum - bNum : bNum - aNum;
        }
        return dir === 'asc' ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
    });

    var tbody = table.tBodies[0];
    rows.forEach(function(row) { tbody.appendChild(row); });
}

function exportCSV() {
    var table = document.getElementById('resultTable');
    var csv = [];
    for (var i = 0; i < table.rows.length; i++) {
        var row = [];
        for (var j = 0; j < table.rows[i].cells.length; j++) {
            row.push('"' + table.rows[i].cells[j].textContent.trim().replace(/"/g, '""') + '"');
        }
        csv.push(row.join(','));
    }
    var blob = new Blob([csv.join('\n')], { type: 'text/csv' });
    var a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'report_' + new Date().toISOString().slice(0,10) + '.csv';
    a.click();
}

function saveCurrentReport() {
    var form = document.getElementById('reportForm');
    var fd = new FormData(form);
    var config = {};
    fd.forEach(function(v, k) {
        if (k.endsWith('[]')) {
            var key = k.replace('[]', '');
            if (!config[key]) config[key] = [];
            config[key].push(v);
        } else {
            config[k] = v;
        }
    });
    document.getElementById('saveReportModal').dataset.config = JSON.stringify(config);
    new bootstrap.Modal(document.getElementById('saveReportModal')).show();
}

function submitSaveReport() {
    var name = document.getElementById('reportName').value;
    if (!name) { alert('Please enter a report name'); return; }
    var config = document.getElementById('saveReportModal').dataset.config;

    fetch('{{ route("analytics.save-report") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({
            report_name: name,
            description: document.getElementById('reportDesc').value,
            config_json: config
        })
    })
    .then(r => r.json())
    .then(function(res) {
        if (res.success) {
            bootstrap.Modal.getInstance(document.getElementById('saveReportModal')).hide();
            showToast('Report saved successfully!', 'success');
        }
    });
}

function showToast(msg, type) {
    var toast = document.createElement('div');
    toast.className = 'alert alert-' + (type || 'success');
    toast.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;border-radius:10px;box-shadow:0 4px 12px rgba(0,0,0,0.15);';
    toast.innerHTML = msg;
    document.body.appendChild(toast);
    setTimeout(function() { toast.remove(); }, 3000);
}
</script>
@endsection
