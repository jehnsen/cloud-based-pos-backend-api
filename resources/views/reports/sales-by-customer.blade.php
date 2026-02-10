@extends('reports.layout')

@section('title', 'Sales by Customer Report')

@section('report-title', 'Sales by Customer Report')

@section('content')
    <div class="summary-box">
        <h3 style="margin-top: 0;">Period: {{ $data['period']['start_date'] }} to {{ $data['period']['end_date'] }}</h3>
    </div>

    <table>
        <thead>
            <tr>
                <th>Customer Code</th>
                <th>Customer Name</th>
                <th class="text-right">Transactions</th>
                <th class="text-right">Total Purchases</th>
                <th class="text-right">Avg Order Value</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['data'] as $row)
            <tr>
                <td>{{ $row['customer']['code'] ?? 'N/A' }}</td>
                <td>{{ $row['customer']['name'] ?? 'N/A' }}</td>
                <td class="text-right">{{ number_format($row['transaction_count']) }}</td>
                <td class="text-right">PHP {{ number_format($row['total_purchases'], 2) }}</td>
                <td class="text-right">PHP {{ number_format($row['average_order_value'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endsection
