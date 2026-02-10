@extends('reports.layout')

@section('title', 'Credit Aging Report')

@section('report-title', 'Credit Aging Report')

@section('content')
    <div class="summary-box">
        <h3 style="margin-top: 0;">Aging Summary</h3>
        <div class="summary-row">
            <span class="summary-label">Current:</span>
            <span>PHP {{ number_format($data['summary']['current'], 2) }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">31-60 Days:</span>
            <span>PHP {{ number_format($data['summary']['days_31_60'], 2) }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">61-90 Days:</span>
            <span>PHP {{ number_format($data['summary']['days_61_90'], 2) }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Over 90 Days:</span>
            <span>PHP {{ number_format($data['summary']['days_over_90'], 2) }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total Outstanding:</span>
            <span>PHP {{ number_format($data['summary']['total_outstanding'], 2) }}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Customer</th>
                <th class="text-right">Current</th>
                <th class="text-right">31-60 Days</th>
                <th class="text-right">61-90 Days</th>
                <th class="text-right">Over 90 Days</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['customers'] as $customer)
            <tr>
                <td>{{ $customer->name }}</td>
                <td class="text-right">PHP {{ number_format($customer->aging_current ?? 0, 2) }}</td>
                <td class="text-right">PHP {{ number_format($customer->aging_31_60 ?? 0, 2) }}</td>
                <td class="text-right">PHP {{ number_format($customer->aging_61_90 ?? 0, 2) }}</td>
                <td class="text-right">PHP {{ number_format($customer->aging_over_90 ?? 0, 2) }}</td>
                <td class="text-right">PHP {{ number_format($customer->total_outstanding, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endsection
