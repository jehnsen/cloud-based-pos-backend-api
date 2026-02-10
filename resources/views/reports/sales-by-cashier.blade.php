@extends('reports.layout')

@section('title', 'Sales by Cashier Report')

@section('report-title', 'Sales by Cashier Report')

@section('content')
    <div class="summary-box">
        <h3 style="margin-top: 0;">Period: {{ $data['period']['start_date'] }} to {{ $data['period']['end_date'] }}</h3>
    </div>

    <table>
        <thead>
            <tr>
                <th>Cashier Name</th>
                <th class="text-right">Transactions</th>
                <th class="text-right">Total Sales</th>
                <th class="text-right">Avg Transaction</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['data'] as $row)
            <tr>
                <td>{{ $row['cashier']['name'] ?? 'N/A' }}</td>
                <td class="text-right">{{ number_format($row['transaction_count']) }}</td>
                <td class="text-right">PHP {{ number_format($row['total_sales'], 2) }}</td>
                <td class="text-right">PHP {{ number_format($row['average_transaction'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endsection
