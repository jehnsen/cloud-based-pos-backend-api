@extends('reports.layout')

@section('title', 'Stock Movement Report')

@section('report-title', 'Stock Movement Report')

@section('content')
    <div class="summary-box">
        <h3 style="margin-top: 0;">Period: {{ $data['period']['start_date'] }} to {{ $data['period']['end_date'] }}</h3>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Product</th>
                <th>Type</th>
                <th class="text-right">Qty Before</th>
                <th class="text-right">Change</th>
                <th class="text-right">Qty After</th>
                <th>Reason</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['data'] as $row)
            <tr>
                <td>{{ \Carbon\Carbon::parse($row['date'])->format('Y-m-d H:i') }}</td>
                <td>{{ $row['product']['name'] ?? 'N/A' }}</td>
                <td>{{ ucfirst($row['type']) }}</td>
                <td class="text-right">{{ number_format($row['quantity_before']) }}</td>
                <td class="text-right" style="color: {{ $row['quantity_change'] >= 0 ? 'green' : 'red' }};">
                    {{ $row['quantity_change'] >= 0 ? '+' : '' }}{{ number_format($row['quantity_change']) }}
                </td>
                <td class="text-right">{{ number_format($row['quantity_after']) }}</td>
                <td>{{ $row['reason'] ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endsection
