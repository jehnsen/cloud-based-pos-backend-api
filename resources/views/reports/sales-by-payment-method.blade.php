@extends('reports.layout')

@section('title', 'Sales by Payment Method Report')

@section('report-title', 'Sales by Payment Method Report')

@section('content')
    <div class="summary-box">
        <h3 style="margin-top: 0;">Period: {{ $data['period']['start_date'] }} to {{ $data['period']['end_date'] }}</h3>
        <div class="summary-row">
            <span class="summary-label">Total Amount:</span>
            <span>PHP {{ number_format($data['total_amount'], 2) }}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Payment Method</th>
                <th class="text-right">Transaction Count</th>
                <th class="text-right">Total Amount</th>
                <th class="text-right">Percentage</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['data'] as $row)
            <tr>
                <td>{{ ucfirst($row['method']) }}</td>
                <td class="text-right">{{ number_format($row['transaction_count']) }}</td>
                <td class="text-right">PHP {{ number_format($row['total_amount'], 2) }}</td>
                <td class="text-right">{{ number_format($row['percentage'], 2) }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endsection
