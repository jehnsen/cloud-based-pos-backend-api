@extends('reports.layout')

@section('title', 'Sales Summary Report')

@section('report-title', 'Sales Summary Report')

@section('content')
    <div class="summary-box">
        <h3 style="margin-top: 0;">Period: {{ $data['period']['start_date'] }} to {{ $data['period']['end_date'] }}</h3>
        <div class="summary-row">
            <span class="summary-label">Total Transactions:</span>
            <span>{{ number_format($data['summary']['total_transactions']) }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total Sales:</span>
            <span>PHP {{ number_format($data['summary']['total_sales'], 2) }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total Discounts:</span>
            <span>PHP {{ number_format($data['summary']['total_discounts'], 2) }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Average Transaction:</span>
            <span>PHP {{ number_format($data['summary']['average_transaction'], 2) }}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Period</th>
                <th class="text-right">Transactions</th>
                <th class="text-right">Total Sales</th>
                <th class="text-right">Discounts</th>
                <th class="text-right">Avg Transaction</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['data'] as $row)
            <tr>
                <td>{{ $row['period'] }}</td>
                <td class="text-right">{{ number_format($row['transaction_count']) }}</td>
                <td class="text-right">PHP {{ number_format($row['total_sales'], 2) }}</td>
                <td class="text-right">PHP {{ number_format($row['total_discounts'], 2) }}</td>
                <td class="text-right">PHP {{ number_format($row['average_transaction'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endsection
