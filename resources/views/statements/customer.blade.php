<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Statement - {{ $statement['customer']['name'] }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
            padding: 20px;
        }

        .header {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #2c3e50;
        }

        .store-info {
            float: left;
            width: 60%;
        }

        .store-name {
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .store-details {
            font-size: 10px;
            color: #666;
            line-height: 1.5;
        }

        .document-title {
            float: right;
            width: 35%;
            text-align: right;
        }

        .document-title h1 {
            font-size: 22px;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .document-title .date {
            font-size: 10px;
            color: #666;
        }

        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }

        .customer-info {
            background: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .customer-info h3 {
            font-size: 12px;
            color: #2c3e50;
            margin-bottom: 8px;
        }

        .info-row {
            margin-bottom: 3px;
            font-size: 10px;
        }

        .info-label {
            display: inline-block;
            width: 120px;
            color: #666;
        }

        .info-value {
            font-weight: 600;
            color: #333;
        }

        .statement-period {
            background: #e3f2fd;
            padding: 10px 15px;
            margin-bottom: 20px;
            border-left: 4px solid #2196f3;
            font-size: 11px;
        }

        .statement-period strong {
            color: #1976d2;
        }

        .balance-summary {
            margin-bottom: 20px;
        }

        .balance-row {
            padding: 8px 15px;
            background: #f8f9fa;
            margin-bottom: 5px;
            border-radius: 3px;
        }

        .balance-row.opening {
            background: #fff3cd;
            border-left: 3px solid #ffc107;
        }

        .balance-row.closing {
            background: #d4edda;
            border-left: 3px solid #28a745;
            font-weight: bold;
            font-size: 12px;
        }

        .balance-label {
            display: inline-block;
            width: 150px;
            color: #666;
        }

        .balance-amount {
            float: right;
            font-weight: 600;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table thead {
            background: #2c3e50;
            color: white;
        }

        table th {
            padding: 10px 8px;
            text-align: left;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
        }

        table tbody tr {
            border-bottom: 1px solid #dee2e6;
        }

        table tbody tr:nth-child(even) {
            background: #f8f9fa;
        }

        table tbody tr:hover {
            background: #e9ecef;
        }

        table td {
            padding: 8px;
            font-size: 10px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .amount-positive {
            color: #d32f2f;
        }

        .amount-negative {
            color: #388e3c;
        }

        .summary-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .summary-box h3 {
            font-size: 12px;
            color: #2c3e50;
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 2px solid #dee2e6;
        }

        .summary-row {
            padding: 6px 0;
            display: flex;
            justify-content: space-between;
            font-size: 10px;
        }

        .summary-row.total {
            font-weight: bold;
            font-size: 11px;
            padding-top: 10px;
            margin-top: 8px;
            border-top: 2px solid #2c3e50;
        }

        .terms-conditions {
            background: #fff3cd;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-size: 9px;
            color: #856404;
        }

        .terms-conditions h4 {
            font-size: 10px;
            margin-bottom: 6px;
            color: #856404;
        }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            font-size: 10px;
            color: #666;
        }

        .footer .thank-you {
            font-size: 12px;
            color: #2c3e50;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .no-transactions {
            text-align: center;
            padding: 40px;
            background: #f8f9fa;
            border-radius: 4px;
            color: #666;
            font-size: 11px;
        }

        @media print {
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header clearfix">
        <div class="store-info">
            <div class="store-name">{{ $store->name ?? 'Hardware Store' }}</div>
            <div class="store-details">
                @if(isset($store->address))
                    {{ $store->address }}<br>
                @endif
                @if(isset($store->phone))
                    Phone: {{ $store->phone }}
                @endif
                @if(isset($store->email))
                    | Email: {{ $store->email }}
                @endif
                @if(isset($store->tin))
                    <br>TIN: {{ $store->tin }}
                @endif
            </div>
        </div>
        <div class="document-title">
            <h1>STATEMENT OF ACCOUNT</h1>
            <div class="date">Generated: {{ date('F d, Y') }}</div>
        </div>
    </div>

    <!-- Customer Information -->
    <div class="customer-info">
        <h3>CUSTOMER INFORMATION</h3>
        <div class="info-row">
            <span class="info-label">Customer Code:</span>
            <span class="info-value">{{ $statement['customer']['code'] }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Customer Name:</span>
            <span class="info-value">{{ $statement['customer']['name'] }}</span>
        </div>
        @if(!empty($statement['customer']['address']))
        <div class="info-row">
            <span class="info-label">Address:</span>
            <span class="info-value">{{ $statement['customer']['address'] }}</span>
        </div>
        @endif
        <div class="info-row">
            <span class="info-label">Phone:</span>
            <span class="info-value">{{ $statement['customer']['phone'] }}</span>
        </div>
        @if(!empty($statement['customer']['email']))
        <div class="info-row">
            <span class="info-label">Email:</span>
            <span class="info-value">{{ $statement['customer']['email'] }}</span>
        </div>
        @endif
        <div class="info-row">
            <span class="info-label">Credit Limit:</span>
            <span class="info-value">₱{{ number_format($statement['customer']['credit_limit'], 2) }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Credit Terms:</span>
            <span class="info-value">{{ $statement['customer']['credit_terms_days'] }} days</span>
        </div>
    </div>

    <!-- Statement Period -->
    <div class="statement-period">
        <strong>Statement Period:</strong> {{ date('F d, Y', strtotime($statement['period']['from'])) }} to {{ date('F d, Y', strtotime($statement['period']['to'])) }}
    </div>

    <!-- Opening Balance -->
    <div class="balance-summary">
        <div class="balance-row opening">
            <span class="balance-label">Opening Balance:</span>
            <span class="balance-amount">₱{{ number_format($statement['opening_balance'], 2) }}</span>
        </div>
    </div>

    <!-- Transactions Table -->
    @if(count($statement['transactions']) > 0)
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Reference</th>
                <th>Description</th>
                <th>Due Date</th>
                <th class="text-right">Charges</th>
                <th class="text-right">Payments</th>
                <th class="text-right">Balance</th>
            </tr>
        </thead>
        <tbody>
            @foreach($statement['transactions'] as $transaction)
            <tr>
                <td>{{ date('M d, Y', strtotime($transaction['date'])) }}</td>
                <td>{{ $transaction['reference'] ?? $transaction['sale_number'] ?? '-' }}</td>
                <td>{{ $transaction['description'] }}</td>
                <td>{{ $transaction['due_date'] ? date('M d, Y', strtotime($transaction['due_date'])) : '-' }}</td>
                <td class="text-right amount-positive">
                    @if($transaction['charges'] > 0)
                        ₱{{ number_format($transaction['charges'], 2) }}
                    @else
                        -
                    @endif
                </td>
                <td class="text-right amount-negative">
                    @if($transaction['payments'] > 0)
                        ₱{{ number_format($transaction['payments'], 2) }}
                    @else
                        -
                    @endif
                </td>
                <td class="text-right">₱{{ number_format($transaction['balance'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="no-transactions">
        No transactions found for the selected period.
    </div>
    @endif

    <!-- Closing Balance -->
    <div class="balance-summary">
        <div class="balance-row closing">
            <span class="balance-label">Closing Balance:</span>
            <span class="balance-amount">₱{{ number_format($statement['closing_balance'], 2) }}</span>
        </div>
    </div>

    <!-- Summary Box -->
    <div class="summary-box">
        <h3>TRANSACTION SUMMARY</h3>
        <div class="summary-row">
            <span>Total Charges:</span>
            <span class="amount-positive">₱{{ number_format($statement['summary']['total_charges'], 2) }}</span>
        </div>
        <div class="summary-row">
            <span>Total Payments:</span>
            <span class="amount-negative">₱{{ number_format($statement['summary']['total_payments'], 2) }}</span>
        </div>
        <div class="summary-row total">
            <span>Net Change:</span>
            <span>₱{{ number_format($statement['summary']['net_change'], 2) }}</span>
        </div>
    </div>

    <!-- Terms and Conditions -->
    <div class="terms-conditions">
        <h4>TERMS AND CONDITIONS</h4>
        <p>
            Payment is due within {{ $statement['customer']['credit_terms_days'] ?? 30 }} days from the invoice date.
            Interest may be charged on overdue accounts. Please ensure timely payment to maintain your credit standing.
            For any discrepancies, please contact us within 7 days of receiving this statement.
        </p>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="thank-you">Thank you for your business!</div>
        <div>For inquiries, please contact us at {{ $store->phone ?? 'N/A' }} or {{ $store->email ?? 'N/A' }}</div>
        <div style="margin-top: 10px; font-size: 9px; color: #999;">
            This is a computer-generated document. No signature is required.
        </div>
    </div>
</body>
</html>
