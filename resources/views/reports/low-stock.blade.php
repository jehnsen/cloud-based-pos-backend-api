@extends('reports.layout')

@section('title', 'Low Stock Alert Report')

@section('report-title', 'Low Stock Alert Report')

@section('content')
    <div class="summary-box">
        <h3 style="margin-top: 0;">Low Stock Summary</h3>
        <div class="summary-row">
            <span class="summary-label">Products Below Reorder Point:</span>
            <span>{{ number_format($data['count']) }}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>SKU</th>
                <th>Product Name</th>
                <th>Category</th>
                <th class="text-right">Current Stock</th>
                <th class="text-right">Reorder Point</th>
                <th class="text-center">Urgency</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['data'] as $row)
            <tr>
                <td>{{ $row['sku'] }}</td>
                <td>{{ $row['name'] }}</td>
                <td>{{ $row['category'] ?? 'N/A' }}</td>
                <td class="text-right">{{ number_format($row['current_stock']) }}</td>
                <td class="text-right">{{ number_format($row['reorder_point']) }}</td>
                <td class="text-center">
                    <strong style="color: {{ $row['urgency'] === 'critical' ? 'red' : ($row['urgency'] === 'high' ? 'orange' : 'black') }};">
                        {{ strtoupper($row['urgency']) }}
                    </strong>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endsection
