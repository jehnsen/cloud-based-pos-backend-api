@extends('reports.layout')

@section('title', 'Product Profitability Report')

@section('report-title', 'Product Profitability Report')

@section('content')
    <div class="summary-box">
        <h3 style="margin-top: 0;">Period: {{ $data['period']['start_date'] }} to {{ $data['period']['end_date'] }}</h3>
        <div class="summary-row">
            <span class="summary-label">Total Revenue:</span>
            <span>PHP {{ number_format($data['summary']['total_revenue'], 2) }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total Cost:</span>
            <span>PHP {{ number_format($data['summary']['total_cost'], 2) }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total Profit:</span>
            <span>PHP {{ number_format($data['summary']['total_profit'], 2) }}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>SKU</th>
                <th>Product Name</th>
                <th class="text-right">Qty Sold</th>
                <th class="text-right">Revenue</th>
                <th class="text-right">Cost</th>
                <th class="text-right">Profit</th>
                <th class="text-right">Margin %</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['data'] as $row)
            <tr>
                <td>{{ $row['product_sku'] }}</td>
                <td>{{ $row['product_name'] }}</td>
                <td class="text-right">{{ number_format($row['quantity_sold']) }}</td>
                <td class="text-right">PHP {{ number_format($row['total_revenue'], 2) }}</td>
                <td class="text-right">PHP {{ number_format($row['total_cost'], 2) }}</td>
                <td class="text-right">PHP {{ number_format($row['gross_profit'], 2) }}</td>
                <td class="text-right">{{ number_format($row['margin_percentage'], 2) }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endsection
