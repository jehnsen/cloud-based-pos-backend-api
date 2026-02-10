@extends('reports.layout')

@section('title', 'Collection Report')

@section('report-title', 'Collection Report')

@section('content')
    <div class="summary-box">
        <h3 style="margin-top: 0;">Period: {{ $data['period']['start_date'] }} to {{ $data['period']['end_date'] }}</h3>
        <div class="summary-row">
            <span class="summary-label">Total Collected:</span>
            <span>PHP {{ number_format($data['summary']['total_collected'], 2) }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total Payments:</span>
            <span>{{ number_format($data['summary']['total_payments']) }}</span>
        </div>
    </div>

    <h3>Summary by Payment Method</h3>
    <table>
        <thead>
            <tr>
                <th>Payment Method</th>
                <th class="text-right">Payment Count</th>
                <th class="text-right">Total Collected</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['by_method'] as $row)
            <tr>
                <td>{{ ucfirst($row['payment_method'] ?? 'N/A') }}</td>
                <td class="text-right">{{ number_format($row['payment_count']) }}</td>
                <td class="text-right">PHP {{ number_format($row['total_collected'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <h3 style="margin-top: 30px;">Daily Collections</h3>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Payment Method</th>
                <th class="text-right">Payment Count</th>
                <th class="text-right">Total Collected</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['daily_collections'] as $row)
            <tr>
                <td>{{ $row['date'] }}</td>
                <td>{{ ucfirst($row['payment_method'] ?? 'N/A') }}</td>
                <td class="text-right">{{ number_format($row['payment_count']) }}</td>
                <td class="text-right">PHP {{ number_format($row['total_collected'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endsection
