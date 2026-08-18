<!DOCTYPE html>
<html>
<head>
    <title>Wake-up Call List</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h2 { margin: 0; }
        .header p { margin: 2px 0; font-size: 11px; }
        .date-range { text-align: center; margin-bottom: 15px; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #999; padding: 5px 8px; text-align: left; font-size: 11px; }
        th { background-color: #0d6efd; color: white; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        .footer { margin-top: 20px; text-align: right; font-size: 10px; }
        @media print { body { margin: 10mm; } }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $company->comp_name ?? 'Analysis HMS' }}</h2>
        <p>{{ $company->address1 ?? '' }} {{ $company->address2 ?? '' }}</p>
        <p>Wake-up Call Register</p>
    </div>
    <div class="date-range">
        From: <strong>{{ $fromdate }}</strong> To: <strong>{{ $todate }}</strong>
    </div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>V.No</th>
                <th>Date</th>
                <th>Time</th>
                <th>Room</th>
                <th>Guest</th>
                <th>Extension</th>
                <th>Reminder</th>
                <th>Food</th>
                <th>Other Request</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $i => $row)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $row->vno }}</td>
                <td>{{ $row->wdate }}</td>
                <td>{{ $row->wtime }}</td>
                <td>{{ $row->roomno }}</td>
                <td>{{ $row->guestprof }}</td>
                <td>{{ $row->extension }}</td>
                <td>{{ $row->remreqd }}</td>
                <td>{{ $row->foodord }}</td>
                <td>{{ $row->otherreq }}</td>
            </tr>
            @empty
            <tr><td colspan="10" style="text-align:center">No records found</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="footer">
        Printed on: {{ date('d/m/Y H:i') }} | {{ $data->count() }} record(s)
    </div>
</body>
</html>
