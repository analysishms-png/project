<!DOCTYPE html>
<html>
<head>
   <meta charset="utf-8">
   <title>Movement List</title>
   <style>
      body { font-family: Arial, sans-serif; font-size: 11px; margin: 20px; }
      .header { text-align: center; margin-bottom: 15px; border-bottom: 2px solid #333; padding-bottom: 10px; }
      .header h2 { margin: 0; font-size: 16px; }
      .header p { margin: 2px 0; font-size: 10px; color: #666; }
      .filters { margin-bottom: 10px; font-size: 10px; color: #555; }
      table { width: 100%; border-collapse: collapse; font-size: 10px; }
      th { background: #333; color: white; padding: 4px 3px; text-align: left; font-size: 9px; }
      td { padding: 3px; border-bottom: 1px solid #ddd; }
      tr:nth-child(even) { background: #f9f9f9; }
      .summary { margin-top: 10px; font-size: 10px; font-weight: bold; }
      .text-right { text-align: right; }
      .text-center { text-align: center; }
      @media print { body { margin: 10px; } }
   </style>
</head>
<body>
   <div class="header">
      <h2>{{ $comp->companyname ?? 'Hotel' }}</h2>
      <p>Movement List</p>
      <p>From: {{ $fromdate }} To: {{ $todate }}</p>
   </div>

   <table>
      <thead>
         <tr>
            <th>#</th>
            <th>Res No</th>
            <th>Guest Name</th>
            <th>Mobile</th>
            <th>Company / TA</th>
            <th>Room Type</th>
            <th>Rooms</th>
            <th>Arr Date</th>
            <th>Dep Date</th>
            <th>Pax</th>
            <th>Plan</th>
            <th>Status</th>
            <th>Advance</th>
         </tr>
      </thead>
      <tbody>
         @foreach($data as $i => $row)
         <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $row->ResNo }}</td>
            <td><b>{{ $row->GuestName }}</b></td>
            <td>{{ $row->MobileNo }}</td>
            <td>{{ $row->Company }}</td>
            <td>{{ $row->RoomType }}</td>
            <td class="text-center">{{ $row->RoomDet }}</td>
            <td>{{ \Carbon\Carbon::parse($row->ArrDate)->format('d/m/Y') }}</td>
            <td>{{ $row->DepDate ? \Carbon\Carbon::parse($row->DepDate)->format('d/m/Y') : '' }}</td>
            <td class="text-center">{{ $row->Pax }}/{{ $row->Child }}</td>
            <td>{{ $row->PlanName }}</td>
            <td>{{ $row->ResStatus ?: 'Confirm' }}</td>
            <td class="text-right">{{ number_format($row->advance, 2) }}</td>
         </tr>
         @endforeach
      </tbody>
   </table>

   <div class="summary">
      Total Bookings: {{ $data->count() }} |
      Total Rooms: {{ $data->sum('RoomDet') }} |
      Total Pax: {{ $data->sum('Pax') + $data->sum('Child') }} |
      Total Advance: ₹{{ number_format($data->sum('advance'), 2) }}
   </div>

   <script>window.onload = function() { window.print(); }</script>
</body>
</html>
