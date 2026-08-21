<!DOCTYPE html>
<html>
<head>
   <title>Denomination Detail - Print</title>
   <style>
      body { font-family: Arial, sans-serif; font-size: 12px; }
      .header { text-align: center; margin-bottom: 20px; }
      .header h2 { margin: 0; }
      .header p { margin: 5px 0; }
      table { width: 100%; border-collapse: collapse; margin-top: 10px; }
      th, td { border: 1px solid #000; padding: 8px; text-align: left; }
      th { background-color: #f0f0f0; }
      .total { font-weight: bold; text-align: right; }
      @media print {
         body { margin: 20px; }
      }
   </style>
</head>
<body>
   <div class="header">
      <h2>{{ $comp->comp_name ?? 'Hotel Name' }}</h2>
      <p>{{ $comp->address1 ?? '' }}, {{ $comp->city ?? '' }}</p>
      <p>Denomination Detail - Serial No: {{ $sno }}</p>
   </div>

   <table>
      <thead>
         <tr>
            <th>#</th>
            <th>Date</th>
            <th>Name</th>
            <th>Denomination Type</th>
            <th>Value</th>
            <th>Unit</th>
            <th>Total</th>
         </tr>
      </thead>
      <tbody>
         @foreach($data as $row)
         <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ date('d-M-Y', strtotime($row->vdate)) }}</td>
            <td>{{ $row->name }}</td>
            <td>{{ $row->denominationtype }}</td>
            <td>₹{{ number_format($row->denominationvalue, 2) }}</td>
            <td>{{ $row->denominationunit }}</td>
            <td>₹{{ number_format($row->denominationtotal, 2) }}</td>
         </tr>
         @endforeach
      </tbody>
      <tfoot>
         <tr>
            <td colspan="6" class="total">Grand Total</td>
            <td class="total">₹{{ number_format($data->sum('denominationtotal'), 2) }}</td>
         </tr>
      </tfoot>
   </table>

   <script>
      window.onload = function() { window.print(); }
   </script>
</body>
</html>
