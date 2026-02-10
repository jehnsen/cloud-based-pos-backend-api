<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt - {{ $sale['number'] }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 10px;
            line-height: 1.3;
            color: #000;
            width: 80mm;
            padding: 5mm;
        }

        .center {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        .logo {
            max-width: 50mm;
            margin: 0 auto 5px;
            display: block;
        }

        .store-name {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .store-info {
            font-size: 8px;
            line-height: 1.2;
            margin-bottom: 3px;
        }

        .separator {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }

        .double-separator {
            border-top: 2px solid #000;
            margin: 5px 0;
        }

        .section {
            margin: 8px 0;
        }

        .row {
            display: flex;
            justify-content: space-between;
            margin: 2px 0;
            font-size: 9px;
        }

        .item-row {
            margin: 3px 0;
        }

        .item-name {
            font-size: 9px;
            font-weight: bold;
        }

        .item-details {
            display: flex;
            justify-content: space-between;
            font-size: 9px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 2px 0;
            font-size: 10px;
        }

        .grand-total {
            font-weight: bold;
            font-size: 12px;
            margin-top: 3px;
        }

        .footer-text {
            text-align: center;
            font-size: 8px;
            margin-top: 5px;
        }

        .watermark {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            color: #ccc;
            margin: 10px 0;
        }

        @page {
            margin: 0;
        }

        @media print {
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    @if($is_voided)
        <div class="watermark">*** VOIDED ***</div>
    @elseif($is_refunded)
        <div class="watermark">** REFUNDED **</div>
    @endif

    <!-- Header -->
    <div class="center">
        @if($store['logo_path'])
            <img src="{{ $store['logo_path'] }}" alt="{{ $store['name'] }}" class="logo">
        @endif
        <div class="store-name">{{ $store['name'] }}</div>
        <div class="store-info">
            {{ $store['address'] }}<br>
            @if($store['city']) {{ $store['city'] }}, @endif
            @if($store['province']) {{ $store['province'] }}<br> @endif
            Tel: {{ $store['phone'] }}
        </div>
        @if($store['is_vat_registered'])
            <div class="store-info">
                VAT TIN: {{ $store['tin'] }}
            </div>
        @endif
    </div>

    <div class="separator"></div>

    <!-- Sale Info -->
    <div class="center bold">SALES INVOICE</div>
    <div class="row">
        <span>Invoice:</span>
        <span>{{ $sale['number'] }}</span>
    </div>
    <div class="row">
        <span>Date:</span>
        <span>{{ $sale['date'] }}</span>
    </div>
    <div class="row">
        <span>Time:</span>
        <span>{{ $sale['time'] }}</span>
    </div>
    <div class="row">
        <span>Cashier:</span>
        <span>{{ $cashier['name'] }}</span>
    </div>

    @if($customer)
        <div class="separator"></div>
        <div class="row">
            <span>Customer:</span>
            <span>{{ $customer['name'] }}</span>
        </div>
    @endif

    <div class="double-separator"></div>

    <!-- Items -->
    @foreach($items as $item)
        <div class="item-row">
            <div class="item-name">{{ $item['product_name'] }}</div>
            <div class="item-details">
                <span>{{ $item['quantity'] }} {{ $item['unit'] }} x {{ $item['unit_price_display'] }}</span>
                <span class="bold">{{ $item['line_total_display'] }}</span>
            </div>
            @if($item['discount_amount'] > 0)
                <div class="item-details" style="color: #666; font-style: italic;">
                    <span>Discount</span>
                    <span>{{ $item['discount_amount_display'] }}</span>
                </div>
            @endif
        </div>
    @endforeach

    @if(count($refunded_items) > 0)
        <div class="separator"></div>
        <div class="center" style="color: #666;">REFUNDED ITEMS</div>
        @foreach($refunded_items as $item)
            <div class="item-row" style="color: #666;">
                <div class="item-name">{{ $item['product_name'] }}</div>
                <div class="item-details">
                    <span>-{{ $item['quantity'] }} {{ $item['unit'] }}</span>
                    <span>-{{ $item['line_total_display'] }}</span>
                </div>
            </div>
        @endforeach
    @endif

    <div class="double-separator"></div>

    <!-- Totals -->
    <div class="total-row">
        <span>Subtotal:</span>
        <span>{{ $pricing['subtotal_display'] }}</span>
    </div>

    @if($pricing['discount_amount'] > 0)
        <div class="total-row">
            <span>Discount:</span>
            <span>{{ $pricing['discount_amount_display'] }}</span>
        </div>
    @endif

    @if($store['is_vat_registered'])
        <div class="total-row">
            <span>VATable Sales:</span>
            <span>{{ $pricing['vat_sales_display'] }}</span>
        </div>
        <div class="total-row">
            <span>VAT ({{ $store['vat_rate'] }}%):</span>
            <span>{{ $pricing['vat_amount_display'] }}</span>
        </div>
    @endif

    <div class="total-row grand-total">
        <span>TOTAL:</span>
        <span>{{ $pricing['total_amount_display'] }}</span>
    </div>

    <div class="separator"></div>

    <!-- Payment -->
    @foreach($payments as $payment)
        <div class="total-row">
            <span>{{ $payment['method'] }}:</span>
            <span>{{ $payment['amount_display'] }}</span>
        </div>
    @endforeach

    @if($totals['change_amount'] > 0)
        <div class="total-row bold">
            <span>CHANGE:</span>
            <span>{{ $totals['change_amount_display'] }}</span>
        </div>
    @endif

    @if(count($refund_payments) > 0)
        <div class="separator"></div>
        @foreach($refund_payments as $payment)
            <div class="total-row">
                <span>Refund ({{ $payment['method'] }}):</span>
                <span>{{ $payment['amount_display'] }}</span>
            </div>
        @endforeach
    @endif

    <div class="separator"></div>

    <!-- Footer -->
    <div class="footer-text">
        <div class="bold">{{ $header_text }}</div>
        <div>{{ $footer_text }}</div>
        <div style="margin-top: 5px; font-size: 7px;">
            {{ $generated_at }}
        </div>
    </div>

    @if($is_voided && $sale['void_reason'])
        <div class="separator"></div>
        <div class="footer-text" style="font-size: 8px;">
            VOID REASON: {{ $sale['void_reason'] }}<br>
            {{ $sale['voided_at'] }}
        </div>
    @endif
</body>
</html>
