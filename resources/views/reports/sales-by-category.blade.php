@extends('reports.layout')

@section('title', 'Sales by Category Report')

@section('report-title', 'Sales by Category Report')

@section('content')
    <div class="summary-box">
        <h3 style="margin-top: 0;">Period: {{ $data['period']['start_date'] }} to {{ $data['period']['end_date'] }}</h3>
        <div class="summary-row">
            <span class="summary-label">Total Sales:</span>
            <span>PHP {{ number_format($data['total_sales'], 2) }}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th class="text-right">Quantity</th>
                <th class="text-right">Total Sales</th>
                <th class="text-right">Transactions</th>
                <th class="text-right">Percentage</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['data'] as $row)
            <tr>
                <td>{{ $row['category_name'] }}</td>
                <td class="text-right">{{ number_format($row['total_quantity']) }}</td>
                <td class="text-right">PHP {{ number_format($row['total_sales'], 2) }}</td>
                <td class="text-right">{{ number_format($row['transaction_count']) }}</td>
                <td class="text-right">{{ number_format($row['percentage'], 2) }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endsection
