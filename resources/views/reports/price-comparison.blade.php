@extends('reports.layout')

@section('title', 'Price Comparison Report')

@section('report-title', 'Price Comparison Report')

@section('content')
    <table>
        <thead>
            <tr>
                <th>Product SKU</th>
                <th>Product Name</th>
                <th>Supplier</th>
                <th class="text-right">Supplier Price</th>
                <th class="text-right">Current Cost</th>
                <th class="text-right">Lead Time</th>
                <th class="text-center">Preferred</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['data'] as $product)
                @foreach($product['suppliers'] as $index => $supplier)
                <tr>
                    @if($index === 0)
                    <td rowspan="{{ count($product['suppliers']) }}">{{ $product['product']['sku'] }}</td>
                    <td rowspan="{{ count($product['suppliers']) }}">{{ $product['product']['name'] }}</td>
                    @endif
                    <td>{{ $supplier['supplier_name'] }}</td>
                    <td class="text-right">PHP {{ number_format($supplier['supplier_price'], 2) }}</td>
                    @if($index === 0)
                    <td rowspan="{{ count($product['suppliers']) }}" class="text-right">
                        PHP {{ number_format($product['product']['current_cost_price'], 2) }}
                    </td>
                    @endif
                    <td class="text-right">{{ $supplier['lead_time_days'] ?? 'N/A' }} days</td>
                    <td class="text-center">{{ $supplier['is_preferred'] ? 'Yes' : 'No' }}</td>
                </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
@endsection
