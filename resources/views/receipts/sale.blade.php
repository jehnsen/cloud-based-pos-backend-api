<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt - {{ $receipt['sale']['sale_number'] }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 10px;
            line-height: 1.4;
            padding: 10px;
            width: 80mm;
        }

        .center {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #000;
        }

        .store-name {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .store-info {
            font-size: 9px;
            margin-bottom: 2px;
        }

        .sale-info {
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #000;
        }

        .sale-info-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }

        .items {
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #000;
        }

        .item {
            margin-bottom: 8px;
        }

        .item-name {
            font-weight: bold;
            margin-bottom: 2px;
        }

        .item-details {
            display: flex;
            justify-content: space-between;
            font-size: 9px;
            margin-bottom: 1px;
        }

        .totals {
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #000;
        }

        .total-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }

        .total-line.grand-total {
            font-weight: bold;
            font-size: 12px;
            margin-top: 5px;
            padding-top: 5px;
            border-top: 1px solid #000;
        }

        .payments {
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #000;
        }

        .payment-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }

        .footer {
            text-align: center;
            font-size: 9px;
            margin-top: 10px;
        }

        .footer-message {
            margin-bottom: 5px;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="store-name">{{ $receipt['store']['name'] }}</div>
        @if($receipt['store']['address'])
            <div class="store-info">{{ $receipt['store']['address'] }}</div>
        @endif
        @if($receipt['store']['phone'])
            <div class="store-info">Tel: {{ $receipt['store']['phone'] }}</div>
        @endif
        @if($receipt['store']['tin'])
            <div class="store-info">TIN: {{ $receipt['store']['tin'] }}</div>
        @endif
        @if($receipt['store']['bir_permit'])
            <div class="store-info">BIR Permit: {{ $receipt['store']['bir_permit'] }}</div>
        @endif
    </div>

    <!-- Sale Info -->
    <div class="sale-info">
        <div class="sale-info-line">
            <span>Receipt No:</span>
            <span class="bold">{{ $receipt['sale']['sale_number'] }}</span>
        </div>
        <div class="sale-info-line">
            <span>Date:</span>
            <span>{{ $receipt['sale']['date'] }}</span>
        </div>
        <div class="sale-info-line">
            <span>Cashier:</span>
            <span>{{ $receipt['sale']['cashier'] }}</span>
        </div>
        @if($receipt['customer'])
            <div class="sale-info-line">
                <span>Customer:</span>
                <span>{{ $receipt['customer']['name'] }}</span>
            </div>
        @endif
    </div>

    <!-- Items -->
    <div class="items">
        @foreach($receipt['items'] as $item)
            <div class="item">
                <div class="item-name">{{ $item['name'] }}</div>
                <div class="item-details">
                    <span>{{ $item['quantity'] }} {{ $item['unit_of_measure'] }} x {{ $item['unit_price'] }}</span>
                    <span>{{ $item['line_total'] }}</span>
                </div>
                @if($item['discount'])
                    <div class="item-details">
                        <span>Discount ({{ $item['discount']['type'] === 'percentage' ? $item['discount']['value'] . '%' : $item['discount']['amount'] }})</span>
                        <span>-{{ $item['discount']['amount'] }}</span>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <!-- Totals -->
    <div class="totals">
        <div class="total-line">
            <span>Subtotal:</span>
            <span>{{ $receipt['totals']['subtotal'] }}</span>
        </div>

        @if($receipt['totals']['discount'])
            <div class="total-line">
                <span>Discount ({{ $receipt['totals']['discount']['type'] === 'percentage' ? $receipt['totals']['discount']['value'] . '%' : 'Fixed' }}):</span>
                <span>-{{ $receipt['totals']['discount']['amount'] }}</span>
            </div>
        @endif

        <div class="total-line">
            <span>{{ $receipt['totals']['vat_type'] }}:</span>
            <span></span>
        </div>
        <div class="total-line">
            <span style="padding-left: 10px;">VATable Sales:</span>
            <span>{{ $receipt['totals']['vat_sales'] }}</span>
        </div>
        <div class="total-line">
            <span style="padding-left: 10px;">VAT ({{ $receipt['totals']['vat_rate'] }}):</span>
            <span>{{ $receipt['totals']['vat_amount'] }}</span>
        </div>

        <div class="total-line grand-total">
            <span>TOTAL:</span>
            <span>{{ $receipt['totals']['total'] }}</span>
        </div>
    </div>

    <!-- Payments -->
    <div class="payments">
        <div class="bold" style="margin-bottom: 5px;">PAYMENT:</div>
        @foreach($receipt['payments'] as $payment)
            <div class="payment-line">
                <span>{{ $payment['method'] }}:</span>
                <span>{{ $payment['amount'] }}</span>
            </div>
            @if($payment['reference'])
                <div class="payment-line" style="font-size: 8px; padding-left: 10px;">
                    <span>Ref: {{ $payment['reference'] }}</span>
                </div>
            @endif
        @endforeach

        @if($receipt['change'])
            <div class="payment-line bold" style="margin-top: 5px;">
                <span>CHANGE:</span>
                <span>{{ $receipt['change'] }}</span>
            </div>
        @endif
    </div>

    <!-- Footer -->
    <div class="footer">
        @if($receipt['notes'])
            <div class="footer-message">{{ $receipt['notes'] }}</div>
            <div class="divider"></div>
        @endif

        <div class="footer-message">{{ $receipt['footer']['message'] }}</div>
        <div class="footer-message">{{ $receipt['footer']['terms'] }}</div>
        <div class="divider"></div>
        <div style="font-size: 8px;">{{ $receipt['footer']['powered_by'] }}</div>

        @if($receipt['is_reprint'])
            <div class="divider"></div>
            <div class="bold">REPRINT</div>
            <div>Printed: {{ $receipt['printed_at'] }}</div>
        @endif
    </div>
</body>
</html>
