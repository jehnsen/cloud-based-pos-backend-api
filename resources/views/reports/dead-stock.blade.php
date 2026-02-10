@extends('reports.layout')

@section('title', 'Dead Stock Report')

@section('report-title', 'Dead Stock Report')

@section('content')
    <div class="summary-box">
        <h3 style="margin-top: 0;">Dead Stock Analysis ({{ $data['period_days'] }} days)</h3>
        <div class="summary-row">
            <span class="summary-label">Products with No Sales:</span>
            <span>{{ number_format($data['count']) }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total Stock Value:</span>
            <span>PHP {{ number_format($data['total_stock_value'], 2) }}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>SKU</th>
                <th>Product Name</th>
                <th>Category</th>
                <th class="text-right">Stock</th>
                <th class="text-right">Stock Value</th>
                <th class="text-right">Days Since Last Sale</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['data'] as $row)
            <tr>
                <td>{{ $row['sku'] }}</td>
                <td>{{ $row['name'] }}</td>
                <td>{{ $row['category'] ?? 'N/A' }}</td>
                <td class="text-right">{{ number_format($row['current_stock']) }}</td>
                <td class="text-right">PHP {{ number_format($row['stock_value'], 2) }}</td>
                <td class="text-right">{{ $row['days_since_last_sale'] ?? 'Never' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endsection
