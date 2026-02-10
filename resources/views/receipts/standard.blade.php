<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - {{ $sale['number'] }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 11px;
            line-height: 1.4;
            color: #000;
            padding: 20px;
        }

        .receipt {
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #000;
            padding: 20px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }

        .logo {
            max-width: 150px;
            margin-bottom: 10px;
        }

        .store-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .store-info {
            font-size: 10px;
            line-height: 1.3;
        }

        .vat-info {
            margin-top: 5px;
            font-size: 9px;
        }

        .section-title {
            font-weight: bold;
            font-size: 12px;
            margin-top: 15px;
            margin-bottom: 5px;
            border-bottom: 1px solid #000;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
            font-size: 10px;
        }

        .info-label {
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }

        th {
            background-color: #f0f0f0;
            padding: 5px;
            text-align: left;
            border-bottom: 2px solid #000;
            font-size: 10px;
        }

        td {
            padding: 4px 5px;
            border-bottom: 1px dotted #ccc;
            font-size: 10px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .totals {
            margin-top: 15px;
            border-top: 2px solid #000;
            padding-top: 10px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 3px 0;
            font-size: 11px;
        }

        .total-row.grand-total {
            font-weight: bold;
            font-size: 14px;
            margin-top: 5px;
            padding-top: 5px;
            border-top: 2px double #000;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #000;
            font-size: 10px;
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 72px;
            font-weight: bold;
            color: rgba(255, 0, 0, 0.1);
            z-index: -1;
        }

        .discount-line {
            color: #c00;
            font-style: italic;
        }

        @media print {
            body {
                padding: 0;
            }
            .receipt {
                border: none;
            }
        }
    </style>
</head>
<body>
    <div class="receipt">
        @if($is_voided)
            <div class="watermark">VOIDED</div>
        @elseif($is_refunded)
            <div class="watermark">REFUNDED</div>
        @endif

        <!-- Header -->
        <div class="header">
            @if($store['logo_path'])
                <img src="{{ $store['logo_path'] }}" alt="{{ $store['name'] }}" class="logo">
            @endif
            <div class="store-name">{{ $store['name'] }}</div>
            <div class="store-info">
                {{ $store['address'] }}<br>
                @if($store['city']) {{ $store['city'] }}, @endif
                @if($store['province']) {{ $store['province'] }}<br> @endif
                Tel: {{ $store['phone'] }} | Email: {{ $store['email'] }}
            </div>
            @if($store['is_vat_registered'])
                <div class="vat-info">
                    <strong>VAT REG TIN:</strong> {{ $store['tin'] }}<br>
                    @if($store['bir_permit_no'])
                        <strong>BIR Permit No:</strong> {{ $store['bir_permit_no'] }}
                    @endif
                </div>
            @endif
        </div>

        <!-- Sale Information -->
        <div class="section-title">SALES INVOICE</div>
        <div class="info-row">
            <span class="info-label">Invoice No:</span>
            <span>{{ $sale['number'] }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Date:</span>
            <span>{{ $sale['date'] }} {{ $sale['time'] }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Cashier:</span>
            <span>{{ $cashier['name'] }}</span>
        </div>
        @if($branch)
            <div class="info-row">
                <span class="info-label">Branch:</span>
                <span>{{ $branch['name'] }}</span>
            </div>
        @endif

        <!-- Customer Information -->
        @if($customer)
            <div class="section-title">CUSTOMER INFORMATION</div>
            <div class="info-row">
                <span class="info-label">Name:</span>
                <span>{{ $customer['name'] }}</span>
            </div>
            @if($customer['code'])
                <div class="info-row">
                    <span class="info-label">Customer ID:</span>
                    <span>{{ $customer['code'] }}</span>
                </div>
            @endif
            @if($customer['tin'])
                <div class="info-row">
                    <span class="info-label">TIN:</span>
                    <span>{{ $customer['tin'] }}</span>
                </div>
            @endif
            @if($customer['address'])
                <div class="info-row">
                    <span class="info-label">Address:</span>
                    <span>{{ $customer['address'] }}</span>
                </div>
            @endif
        @endif

        <!-- Items -->
        <div class="section-title">ITEMS</div>
        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th class="text-center">Qty</th>
                    <th class="text-right">Price</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                    <tr>
                        <td>
                            {{ $item['product_name'] }}
                            @if($item['sku'])
                                <br><small style="color: #666;">SKU: {{ $item['sku'] }}</small>
                            @endif
                        </td>
                        <td class="text-center">{{ $item['quantity'] }} {{ $item['unit'] }}</td>
                        <td class="text-right">{{ $item['unit_price_display'] }}</td>
                        <td class="text-right">{{ $item['line_total_display'] }}</td>
                    </tr>
                    @if($item['discount_amount'] > 0)
                        <tr class="discount-line">
                            <td colspan="3" style="text-align: right; padding-left: 20px;">
                                Discount
                                @if($item['discount_type'] === 'percentage')
                                    ({{ $item['discount_value'] }}%)
                                @endif
                            </td>
                            <td class="text-right">{{ $item['discount_amount_display'] }}</td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>

        <!-- Refunded Items -->
        @if(count($refunded_items) > 0)
            <div class="section-title" style="color: #c00;">REFUNDED ITEMS</div>
            <table>
                <tbody>
                    @foreach($refunded_items as $item)
                        <tr style="color: #c00;">
                            <td>{{ $item['product_name'] }}</td>
                            <td class="text-center">-{{ $item['quantity'] }} {{ $item['unit'] }}</td>
                            <td class="text-right">{{ $item['unit_price_display'] }}</td>
                            <td class="text-right">-{{ $item['line_total_display'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <!-- Totals -->
        <div class="totals">
            <div class="total-row">
                <span>Subtotal:</span>
                <span>{{ $pricing['subtotal_display'] }}</span>
            </div>

            @if($pricing['discount_amount'] > 0)
                <div class="total-row discount-line">
                    <span>
                        Discount
                        @if($pricing['discount_type'] === 'percentage')
                            ({{ $pricing['discount_value'] }}%)
                        @endif
                    </span>
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
                @if($pricing['vat_exempt_sales'] > 0)
                    <div class="total-row">
                        <span>VAT-Exempt Sales:</span>
                        <span>{{ $pricing['vat_exempt_sales_display'] }}</span>
                    </div>
                @endif
            @endif

            <div class="total-row grand-total">
                <span>TOTAL AMOUNT:</span>
                <span>{{ $pricing['total_amount_display'] }}</span>
            </div>
        </div>

        <!-- Payments -->
        <div class="section-title">PAYMENT DETAILS</div>
        @foreach($payments as $payment)
            <div class="total-row">
                <span>
                    {{ $payment['method'] }}
                    @if($payment['reference_number'])
                        ({{ $payment['reference_number'] }})
                    @endif
                </span>
                <span>{{ $payment['amount_display'] }}</span>
            </div>
        @endforeach

        @if($totals['change_amount'] > 0)
            <div class="total-row" style="font-weight: bold; margin-top: 5px;">
                <span>CHANGE:</span>
                <span>{{ $totals['change_amount_display'] }}</span>
            </div>
        @endif

        @if(count($refund_payments) > 0)
            <div class="section-title" style="color: #c00; margin-top: 10px;">REFUND DETAILS</div>
            @foreach($refund_payments as $payment)
                <div class="total-row" style="color: #c00;">
                    <span>
                        {{ $payment['method'] }}
                        @if($payment['reference_number'])
                            ({{ $payment['reference_number'] }})
                        @endif
                    </span>
                    <span>{{ $payment['amount_display'] }}</span>
                </div>
            @endforeach
        @endif

        <!-- Notes -->
        @if($sale['notes'])
            <div class="section-title">NOTES</div>
            <p style="font-size: 10px; margin: 5px 0;">{{ $sale['notes'] }}</p>
        @endif

        @if($is_voided && $sale['void_reason'])
            <div class="section-title" style="color: #c00;">VOID REASON</div>
            <p style="font-size: 10px; margin: 5px 0; color: #c00;">
                {{ $sale['void_reason'] }}<br>
                <small>Voided at: {{ $sale['voided_at'] }}</small>
            </p>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p style="font-weight: bold; margin-bottom: 10px;">{{ $header_text }}</p>
            <p>{{ $footer_text }}</p>
            <p style="margin-top: 10px; font-size: 9px;">
                This serves as your Official Receipt.<br>
                Generated on {{ $generated_at }}
            </p>
            @if($store['is_vat_registered'])
                <p style="margin-top: 5px; font-size: 8px; font-style: italic;">
                    "This invoice/receipt shall be valid for five (5) years from the date of the permit to use."
                </p>
            @endif
        </div>
    </div>
</body>
</html>
