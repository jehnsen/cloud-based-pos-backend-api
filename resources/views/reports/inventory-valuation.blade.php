@extends('reports.layout')

@section('title', 'Inventory Valuation Report')

@section('report-title', 'Inventory Valuation Report')

@section('content')
    <div class="summary-box">
        <h3 style="margin-top: 0;">Inventory Summary</h3>
        <div class="summary-row">
            <span class="summary-label">Total Products:</span>
            <span>{{ number_format($data['summary']['total_products']) }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total Units:</span>
            <span>{{ number_format($data['summary']['total_units']) }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total Value:</span>
            <span>PHP {{ number_format($data['summary']['total_value'], 2) }}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th class="text-right">Product Count</th>
                <th class="text-right">Total Units</th>
                <th class="text-right">Total Value</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['data'] as $row)
            <tr>
                <td>{{ $row['category_name'] }}</td>
                <td class="text-right">{{ number_format($row['product_count']) }}</td>
                <td class="text-right">{{ number_format($row['total_units']) }}</td>
                <td class="text-right">PHP {{ number_format($row['total_value'], 2) }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td>TOTAL</td>
                <td class="text-right">{{ number_format($data['summary']['total_products']) }}</td>
                <td class="text-right">{{ number_format($data['summary']['total_units']) }}</td>
                <td class="text-right">PHP {{ number_format($data['summary']['total_value'], 2) }}</td>
            </tr>
        </tbody>
    </table>
@endsection
