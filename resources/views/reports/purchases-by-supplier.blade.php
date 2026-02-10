@extends('reports.layout')

@section('title', 'Purchases by Supplier Report')

@section('report-title', 'Purchases by Supplier Report')

@section('content')
    <div class="summary-box">
        <h3 style="margin-top: 0;">Period: {{ $data['period']['start_date'] }} to {{ $data['period']['end_date'] }}</h3>
        <div class="summary-row">
            <span class="summary-label">Total Purchase Orders:</span>
            <span>{{ number_format($data['summary']['total_pos']) }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total Amount:</span>
            <span>PHP {{ number_format($data['summary']['total_amount'], 2) }}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Supplier Code</th>
                <th>Supplier Name</th>
                <th class="text-right">PO Count</th>
                <th class="text-right">Total Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['data'] as $row)
            <tr>
                <td>{{ $row['supplier']['code'] ?? 'N/A' }}</td>
                <td>{{ $row['supplier']['name'] ?? 'N/A' }}</td>
                <td class="text-right">{{ number_format($row['po_count']) }}</td>
                <td class="text-right">PHP {{ number_format($row['total_amount'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endsection
