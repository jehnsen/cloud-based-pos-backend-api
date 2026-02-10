<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Purchase Order - {{ $purchaseOrder->po_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 11px;
            line-height: 1.5;
            padding: 20px;
            color: #333;
        }

        .header {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #333;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .company-info {
            flex: 1;
        }

        .company-name {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #000;
        }

        .company-details {
            font-size: 10px;
            line-height: 1.4;
        }

        .po-title {
            flex: 1;
            text-align: right;
        }

        .po-title h1 {
            font-size: 24px;
            font-weight: bold;
            color: #000;
            margin-bottom: 5px;
        }

        .po-number {
            font-size: 14px;
            color: #666;
        }

        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 25px;
        }

        .info-box {
            flex: 1;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }

        .info-box + .info-box {
            margin-left: 15px;
        }

        .info-box h3 {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #000;
            text-transform: uppercase;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }

        .info-line {
            margin-bottom: 5px;
            font-size: 10px;
        }

        .info-label {
            font-weight: bold;
            color: #555;
            display: inline-block;
            width: 120px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }

        .items-table thead {
            background: #333;
            color: #fff;
        }

        .items-table th {
            padding: 10px 8px;
            text-align: left;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .items-table th.right {
            text-align: right;
        }

        .items-table th.center {
            text-align: center;
        }

        .items-table tbody tr {
            border-bottom: 1px solid #ddd;
        }

        .items-table tbody tr:last-child {
            border-bottom: 2px solid #333;
        }

        .items-table td {
            padding: 10px 8px;
            font-size: 10px;
        }

        .items-table td.right {
            text-align: right;
        }

        .items-table td.center {
            text-align: center;
        }

        .product-name {
            font-weight: bold;
            color: #000;
        }

        .product-sku {
            font-size: 9px;
            color: #666;
        }

        .totals-section {
            float: right;
            width: 300px;
            margin-bottom: 25px;
        }

        .total-line {
            display: flex;
            justify-content: space-between;
            padding: 8px 15px;
            font-size: 11px;
        }

        .total-line.grand-total {
            background: #333;
            color: #fff;
            font-size: 14px;
            font-weight: bold;
            margin-top: 5px;
        }

        .notes-section {
            clear: both;
            margin-bottom: 25px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }

        .notes-section h3 {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 8px;
            color: #000;
            text-transform: uppercase;
        }

        .notes-content {
            font-size: 10px;
            line-height: 1.6;
            color: #555;
        }

        .terms-section {
            margin-bottom: 25px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .terms-section h3 {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #000;
            text-transform: uppercase;
        }

        .terms-list {
            list-style-position: inside;
            font-size: 9px;
            line-height: 1.8;
            color: #555;
        }

        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 50px;
            padding-top: 30px;
        }

        .signature-box {
            flex: 1;
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #333;
            padding-top: 5px;
            margin-top: 40px;
            font-size: 10px;
            font-weight: bold;
        }

        .signature-label {
            font-size: 9px;
            color: #666;
            margin-top: 3px;
        }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 9px;
            color: #999;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-draft {
            background: #ffc107;
            color: #000;
        }

        .status-submitted {
            background: #17a2b8;
            color: #fff;
        }

        .status-partial {
            background: #fd7e14;
            color: #fff;
        }

        .status-received {
            background: #28a745;
            color: #fff;
        }

        .status-cancelled {
            background: #dc3545;
            color: #fff;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <div class="company-info">
                <div class="company-name">{{ $purchaseOrder->store->name ?? 'Store Name' }}</div>
                <div class="company-details">
                    @if($purchaseOrder->store->address ?? null)
                        {{ $purchaseOrder->store->address }}<br>
                    @endif
                    @if($purchaseOrder->store->city ?? null)
                        {{ $purchaseOrder->store->city }}, {{ $purchaseOrder->store->province ?? '' }} {{ $purchaseOrder->store->postal_code ?? '' }}<br>
                    @endif
                    @if($purchaseOrder->store->phone ?? null)
                        Tel: {{ $purchaseOrder->store->phone }}<br>
                    @endif
                    @if($purchaseOrder->store->email ?? null)
                        Email: {{ $purchaseOrder->store->email }}<br>
                    @endif
                    @if($purchaseOrder->store->tin ?? null)
                        TIN: {{ $purchaseOrder->store->tin }}
                    @endif
                </div>
            </div>
            <div class="po-title">
                <h1>PURCHASE ORDER</h1>
                <div class="po-number">{{ $purchaseOrder->po_number }}</div>
                <div style="margin-top: 10px;">
                    <span class="status-badge status-{{ $purchaseOrder->status }}">
                        {{ strtoupper($purchaseOrder->status) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Section -->
    <div class="info-section">
        <!-- Supplier Information -->
        <div class="info-box">
            <h3>Supplier</h3>
            <div class="info-line">
                <strong style="font-size: 12px;">{{ $purchaseOrder->supplier->name }}</strong>
            </div>
            @if($purchaseOrder->supplier->contact_person)
                <div class="info-line">
                    <span class="info-label">Attention:</span>
                    {{ $purchaseOrder->supplier->contact_person }}
                </div>
            @endif
            @if($purchaseOrder->supplier->address)
                <div class="info-line">
                    <span class="info-label">Address:</span>
                    {{ $purchaseOrder->supplier->address }}
                </div>
            @endif
            @if($purchaseOrder->supplier->city)
                <div class="info-line">
                    {{ $purchaseOrder->supplier->city }}, {{ $purchaseOrder->supplier->province ?? '' }}
                </div>
            @endif
            @if($purchaseOrder->supplier->phone)
                <div class="info-line">
                    <span class="info-label">Phone:</span>
                    {{ $purchaseOrder->supplier->phone }}
                </div>
            @endif
            @if($purchaseOrder->supplier->email)
                <div class="info-line">
                    <span class="info-label">Email:</span>
                    {{ $purchaseOrder->supplier->email }}
                </div>
            @endif
            @if($purchaseOrder->supplier->tin)
                <div class="info-line">
                    <span class="info-label">TIN:</span>
                    {{ $purchaseOrder->supplier->tin }}
                </div>
            @endif
        </div>

        <!-- Order Information -->
        <div class="info-box">
            <h3>Order Information</h3>
            <div class="info-line">
                <span class="info-label">Order Date:</span>
                {{ $purchaseOrder->order_date?->format('F d, Y') ?? 'N/A' }}
            </div>
            <div class="info-line">
                <span class="info-label">Expected Delivery:</span>
                {{ $purchaseOrder->expected_delivery_date?->format('F d, Y') ?? 'TBD' }}
            </div>
            @if($purchaseOrder->received_date)
                <div class="info-line">
                    <span class="info-label">Received Date:</span>
                    {{ $purchaseOrder->received_date->format('F d, Y') }}
                </div>
            @endif
            @if($purchaseOrder->branch)
                <div class="info-line">
                    <span class="info-label">Deliver To:</span>
                    {{ $purchaseOrder->branch->name }}
                </div>
            @endif
            @if($purchaseOrder->user)
                <div class="info-line">
                    <span class="info-label">Prepared By:</span>
                    {{ $purchaseOrder->user->name }}
                </div>
            @endif
            @if($purchaseOrder->supplier->payment_terms_days)
                <div class="info-line">
                    <span class="info-label">Payment Terms:</span>
                    {{ $purchaseOrder->supplier->payment_terms_days }} days
                </div>
            @endif
        </div>
    </div>

    <!-- Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 40%;">Product Description</th>
                <th class="center" style="width: 10%;">Unit</th>
                <th class="right" style="width: 10%;">Qty Ordered</th>
                <th class="right" style="width: 10%;">Qty Received</th>
                <th class="right" style="width: 10%;">Unit Cost</th>
                <th class="right" style="width: 15%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchaseOrder->purchaseOrderItems as $index => $item)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>
                        <div class="product-name">{{ $item->product_name }}</div>
                        <div class="product-sku">SKU: {{ $item->product_sku }}</div>
                    </td>
                    <td class="center">{{ $item->product->unit->abbreviation ?? 'PC' }}</td>
                    <td class="right">{{ number_format($item->quantity_ordered, 2) }}</td>
                    <td class="right">{{ number_format($item->quantity_received, 2) }}</td>
                    <td class="right">₱{{ number_format($item->unit_price, 2) }}</td>
                    <td class="right">₱{{ number_format($item->line_total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals -->
    <div class="totals-section">
        <div class="total-line grand-total">
            <span>TOTAL AMOUNT</span>
            <span>₱{{ number_format($purchaseOrder->total_amount, 2) }}</span>
        </div>
    </div>

    <!-- Notes -->
    @if($purchaseOrder->notes)
        <div class="notes-section">
            <h3>Notes / Special Instructions</h3>
            <div class="notes-content">{{ $purchaseOrder->notes }}</div>
        </div>
    @endif

    <!-- Terms and Conditions -->
    <div class="terms-section">
        <h3>Terms and Conditions</h3>
        <ul class="terms-list">
            <li>Please confirm receipt of this purchase order and expected delivery date.</li>
            <li>All items must be delivered in good condition and match the specifications listed above.</li>
            <li>Any discrepancies or damages must be reported immediately upon delivery.</li>
            <li>Payment will be processed according to agreed payment terms after receipt and inspection of goods.</li>
            <li>Supplier must provide original invoice and delivery receipt upon delivery.</li>
            <li>Any changes to this order must be authorized in writing by our authorized representative.</li>
        </ul>
    </div>

    <!-- Signatures -->
    <div class="signatures">
        <div class="signature-box">
            <div class="signature-line">
                {{ $purchaseOrder->user->name ?? '_________________________' }}
            </div>
            <div class="signature-label">Prepared By</div>
        </div>
        <div class="signature-box">
            <div class="signature-line">_________________________</div>
            <div class="signature-label">Approved By</div>
        </div>
        <div class="signature-box">
            <div class="signature-line">_________________________</div>
            <div class="signature-label">Received By (Supplier)</div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        This is a computer-generated document. No signature is required.<br>
        Generated on {{ now()->format('F d, Y h:i A') }}
    </div>
</body>
</html>
