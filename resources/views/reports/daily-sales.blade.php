@extends('reports.layout')

@section('title', 'Daily Sales Report')

@section('report-title', 'Daily Sales Report')

@section('content')
    <div class="summary-box">
        <h3 style="margin-top: 0;">Date: {{ $data['date'] }}</h3>
        <div class="summary-row">
            <span class="summary-label">Total Transactions:</span>
            <span>{{ number_format($data['summary']['transaction_count']) }}</span>
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

    <h3>Hourly Breakdown</h3>
    <table>
        <thead>
            <tr>
                <th>Hour</th>
                <th class="text-right">Transactions</th>
                <th class="text-right">Total Sales</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['hourly_breakdown'] as $row)
            <tr>
                <td>{{ $row['hour'] }}:00</td>
                <td class="text-right">{{ number_format($row['transaction_count']) }}</td>
                <td class="text-right">PHP {{ number_format($row['total_sales'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <h3 style="margin-top: 30px;">Payment Methods</h3>
    <table>
        <thead>
            <tr>
                <th>Payment Method</th>
                <th class="text-right">Count</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['payment_methods'] as $row)
            <tr>
                <td>{{ ucfirst($row['method']) }}</td>
                <td class="text-right">{{ number_format($row['count']) }}</td>
                <td class="text-right">PHP {{ number_format($row['total'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <h3 style="margin-top: 30px;">Top Products</h3>
    <table>
        <thead>
            <tr>
                <th>SKU</th>
                <th>Product Name</th>
                <th class="text-right">Quantity Sold</th>
                <th class="text-right">Revenue</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['top_products'] as $row)
            <tr>
                <td>{{ $row['product_sku'] }}</td>
                <td>{{ $row['product_name'] }}</td>
                <td class="text-right">{{ number_format($row['quantity_sold']) }}</td>
                <td class="text-right">PHP {{ number_format($row['revenue'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endsection
