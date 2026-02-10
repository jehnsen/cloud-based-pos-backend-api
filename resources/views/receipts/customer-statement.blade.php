<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Statement - {{ $customer['name'] }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #000;
            padding: 30px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 3px solid #000;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .logo {
            max-width: 100px;
        }

        .store-info {
            flex: 1;
            padding: 0 20px;
        }

        .store-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .document-title {
            text-align: right;
            font-size: 22px;
            font-weight: bold;
            color: #333;
        }

        .period {
            text-align: right;
            font-size: 11px;
            color: #666;
            margin-top: 5px;
        }

        .customer-section {
            background-color: #f0f0f0;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 5px solid #333;
        }

        .customer-name {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .customer-details {
            display: grid;
            grid-template-columns: 150px 1fr;
            gap: 5px;
            font-size: 10px;
        }

        .label {
            font-weight: bold;
        }

        .summary-boxes {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }

        .summary-box {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
            background-color: #f9f9f9;
        }

        .summary-label {
            font-size: 9px;
            color: #666;
            margin-bottom: 5px;
        }

        .summary-amount {
            font-size: 14px;
            font-weight: bold;
            color: #333;
        }

        .summary-box.negative .summary-amount {
            color: #c00;
        }

        .summary-box.positive .summary-amount {
            color: #0a0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 9px;
        }

        th {
            background-color: #333;
            color: white;
            padding: 6px;
            text-align: left;
            font-size: 9px;
        }

        td {
            padding: 5px 6px;
            border-bottom: 1px solid #ddd;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .aging-section {
            margin: 20px 0;
            padding: 15px;
            background-color: #fff3cd;
            border: 1px solid #ffc107;
        }

        .aging-title {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .aging-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
        }

        .aging-bucket {
            text-align: center;
            padding: 8px;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 3px;
        }

        .aging-bucket-label {
            font-size: 9px;
            color: #666;
            margin-bottom: 3px;
        }

        .aging-bucket-amount {
            font-size: 11px;
            font-weight: bold;
        }

        .totals-section {
            margin-top: 20px;
            padding: 15px;
            background-color: #f0f0f0;
            border: 2px solid #333;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            font-size: 11px;
        }

        .total-row.grand {
            font-size: 14px;
            font-weight: bold;
            border-top: 2px solid #333;
            margin-top: 10px;
            padding-top: 10px;
        }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ccc;
            text-align: center;
            font-size: 9px;
            color: #666;
        }

        .overdue-warning {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            margin: 15px 0;
            border: 1px solid #f5c6cb;
            border-radius: 3px;
            text-align: center;
            font-weight: bold;
        }

        @media print {
            body {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div>
            @if($store['logo_path'])
                <img src="{{ $store['logo_path'] }}" alt="{{ $store['name'] }}" class="logo">
            @endif
        </div>
        <div class="store-info">
            <div class="store-name">{{ $store['name'] }}</div>
            <div>{{ $store['address'] }}</div>
            <div>Tel: {{ $store['phone'] }} | Email: {{ $store['email'] }}</div>
        </div>
        <div>
            <div class="document-title">STATEMENT OF ACCOUNT</div>
            <div class="period">
                {{ $statement['start_date'] }} to {{ $statement['end_date'] }}
            </div>
        </div>
    </div>

    <!-- Customer Information -->
    <div class="customer-section">
        <div class="customer-name">{{ $customer['name'] }}</div>
        <div class="customer-details">
            <div class="label">Customer Code:</div>
            <div>{{ $customer['code'] }}</div>

            @if($customer['address'])
                <div class="label">Address:</div>
                <div>{{ $customer['address'] }}</div>
            @endif

            <div class="label">Contact:</div>
            <div>{{ $customer['phone'] }} @if($customer['email']) | {{ $customer['email'] }} @endif</div>

            <div class="label">Credit Limit:</div>
            <div>{{ $customer['credit_limit'] }}</div>

            <div class="label">Credit Terms:</div>
            <div>{{ $customer['credit_terms_days'] }} days</div>
        </div>
    </div>

    <!-- Summary Boxes -->
    <div class="summary-boxes">
        <div class="summary-box">
            <div class="summary-label">Opening Balance</div>
            <div class="summary-amount">{{ $statement['opening_balance'] }}</div>
        </div>
        <div class="summary-box negative">
            <div class="summary-label">Total Charges</div>
            <div class="summary-amount">{{ $statement['total_charges'] }}</div>
        </div>
        <div class="summary-box positive">
            <div class="summary-label">Total Payments</div>
            <div class="summary-amount">{{ $statement['total_payments'] }}</div>
        </div>
        <div class="summary-box">
            <div class="summary-label">Closing Balance</div>
            <div class="summary-amount">{{ $statement['closing_balance'] }}</div>
        </div>
    </div>

    <!-- Aging Analysis -->
    @if($aging)
        <div class="aging-section">
            <div class="aging-title">AGING ANALYSIS</div>
            <div class="aging-grid">
                <div class="aging-bucket">
                    <div class="aging-bucket-label">Current<br>(0-30 days)</div>
                    <div class="aging-bucket-amount">{{ $aging['current'] }}</div>
                </div>
                <div class="aging-bucket">
                    <div class="aging-bucket-label">31-60 Days</div>
                    <div class="aging-bucket-amount">{{ $aging['31_60'] }}</div>
                </div>
                <div class="aging-bucket">
                    <div class="aging-bucket-label">61-90 Days</div>
                    <div class="aging-bucket-amount">{{ $aging['61_90'] }}</div>
                </div>
                <div class="aging-bucket">
                    <div class="aging-bucket-label">Over 90 Days</div>
                    <div class="aging-bucket-amount">{{ $aging['over_90'] }}</div>
                </div>
                <div class="aging-bucket" style="background-color: #fff; border: 2px solid #333;">
                    <div class="aging-bucket-label"><strong>Total Outstanding</strong></div>
                    <div class="aging-bucket-amount" style="font-size: 13px;">{{ $aging['total'] }}</div>
                </div>
            </div>
        </div>
    @endif

    <!-- Overdue Warning -->
    @php
        $hasOverdue = floatval(str_replace(['₱', ','], '', $aging['over_90'] ?? '0')) > 0;
    @endphp
    @if($hasOverdue)
        <div class="overdue-warning">
            ⚠ ATTENTION: You have overdue charges more than 90 days old. Please settle immediately.
        </div>
    @endif

    <!-- Transaction History -->
    <div style="margin-top: 20px;">
        <div style="font-size: 12px; font-weight: bold; margin-bottom: 10px;">TRANSACTION HISTORY</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 80px;">Date</th>
                    <th style="width: 80px;">Type</th>
                    <th>Description</th>
                    <th style="width: 100px;">Reference</th>
                    <th style="width: 90px; text-align: right;">Charges</th>
                    <th style="width: 90px; text-align: right;">Payments</th>
                    <th style="width: 100px; text-align: right;">Balance</th>
                </tr>
            </thead>
            <tbody>
                @if(count($transactions) === 0)
                    <tr>
                        <td colspan="7" class="text-center" style="padding: 20px; color: #999;">
                            No transactions found for this period.
                        </td>
                    </tr>
                @else
                    @foreach($transactions as $transaction)
                        <tr>
                            <td>{{ $transaction['date'] }}</td>
                            <td>{{ $transaction['type'] }}</td>
                            <td>{{ $transaction['description'] }}</td>
                            <td>{{ $transaction['reference'] }}</td>
                            <td class="text-right" style="color: #c00;">{{ $transaction['charges'] }}</td>
                            <td class="text-right" style="color: #0a0;">{{ $transaction['payments'] }}</td>
                            <td class="text-right"><strong>{{ $transaction['balance'] }}</strong></td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>

    <!-- Totals Section -->
    <div class="totals-section">
        <div class="total-row">
            <span>Opening Balance:</span>
            <span>{{ $statement['opening_balance'] }}</span>
        </div>
        <div class="total-row">
            <span>Total Charges (Credits Extended):</span>
            <span style="color: #c00;">{{ $statement['total_charges'] }}</span>
        </div>
        <div class="total-row">
            <span>Total Payments (Credits Received):</span>
            <span style="color: #0a0;">{{ $statement['total_payments'] }}</span>
        </div>
        <div class="total-row grand">
            <span>CLOSING BALANCE:</span>
            <span>{{ $statement['closing_balance'] }}</span>
        </div>
    </div>

    <!-- Payment Instructions -->
    <div style="margin-top: 20px; padding: 15px; background-color: #f9f9f9; border: 1px solid #ddd;">
        <div style="font-weight: bold; margin-bottom: 10px;">PAYMENT INSTRUCTIONS</div>
        <div style="font-size: 9px; line-height: 1.6;">
            <p>Please make checks payable to: <strong>{{ $store['name'] }}</strong></p>
            <p style="margin-top: 5px;">For bank transfers or online payments, please contact our office for account details.</p>
            <p style="margin-top: 5px;">Contact: {{ $store['phone'] }} | {{ $store['email'] }}</p>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p style="font-weight: bold; margin-bottom: 5px;">This is a computer-generated statement of account.</p>
        <p>Generated on {{ $generated_at }}</p>
        <p style="margin-top: 10px;">
            If you have any questions about this statement, please contact our accounting department<br>
            at {{ $store['phone'] }} or {{ $store['email'] }}
        </p>
        <p style="margin-top: 15px; font-size: 8px; font-style: italic;">
            Please verify this statement and notify us of any discrepancies within 7 days from receipt.
        </p>
    </div>
</body>
</html>
