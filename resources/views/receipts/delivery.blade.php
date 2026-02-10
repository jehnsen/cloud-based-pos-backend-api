<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delivery Receipt - {{ $delivery['number'] }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.5;
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
            max-width: 120px;
        }

        .store-info {
            flex: 1;
            padding: 0 20px;
        }

        .store-name {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .document-title {
            text-align: right;
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }

        .document-number {
            text-align: right;
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }

        .section {
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 12px;
            font-weight: bold;
            background-color: #f0f0f0;
            padding: 5px 10px;
            margin-bottom: 10px;
            border-left: 4px solid #333;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 150px 1fr;
            gap: 8px;
        }

        .info-label {
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th {
            background-color: #333;
            color: white;
            padding: 8px;
            text-align: left;
            font-size: 11px;
        }

        td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .signature-section {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 30px;
            margin-top: 40px;
        }

        .signature-box {
            border: 1px solid #000;
            padding: 15px;
            min-height: 100px;
        }

        .signature-label {
            font-weight: bold;
            margin-bottom: 10px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }

        .signature-line {
            border-bottom: 1px solid #000;
            margin-top: 40px;
            margin-bottom: 5px;
        }

        .signature-text {
            text-align: center;
            font-size: 9px;
            color: #666;
        }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ccc;
            text-align: center;
            font-size: 9px;
            color: #666;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 10px;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-in-transit {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .status-delivered {
            background-color: #d4edda;
            color: #155724;
        }

        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
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
    <div class="header">
        <div>
            @if($store['logo_path'])
                <img src="{{ $store['logo_path'] }}" alt="{{ $store['name'] }}" class="logo">
            @endif
        </div>
        <div class="store-info">
            <div class="store-name">{{ $store['name'] }}</div>
            <div>{{ $store['address'] }}</div>
            @if($store['city'])
                <div>{{ $store['city'] }}, {{ $store['province'] }}</div>
            @endif
            <div>Tel: {{ $store['phone'] }} | Email: {{ $store['email'] }}</div>
        </div>
        <div>
            <div class="document-title">DELIVERY RECEIPT</div>
            <div class="document-number">{{ $delivery['number'] }}</div>
            <div style="margin-top: 10px; text-align: right;">
                <span class="status-badge status-{{ strtolower(str_replace(' ', '-', $delivery['status'])) }}">
                    {{ $delivery['status'] }}
                </span>
            </div>
        </div>
    </div>

    <!-- Delivery Information -->
    <div class="section">
        <div class="section-title">DELIVERY INFORMATION</div>
        <div class="info-grid">
            <div class="info-label">Scheduled Date:</div>
            <div>{{ $delivery['date'] }}</div>

            @if($delivery['delivered_at'])
                <div class="info-label">Delivered At:</div>
                <div>{{ $delivery['delivered_at'] }}</div>
            @endif

            @if($sale)
                <div class="info-label">Related Invoice:</div>
                <div>{{ $sale['number'] }} ({{ $sale['date'] }})</div>
            @endif
        </div>
    </div>

    <!-- Customer Information -->
    <div class="section">
        <div class="section-title">DELIVER TO</div>
        <div class="info-grid">
            <div class="info-label">Customer Name:</div>
            <div><strong>{{ $customer['name'] }}</strong></div>

            <div class="info-label">Contact Number:</div>
            <div>{{ $customer['phone'] }}</div>

            <div class="info-label">Delivery Address:</div>
            <div>{{ $customer['delivery_address'] }}</div>
        </div>
    </div>

    <!-- Driver Information -->
    @if($driver)
        <div class="section">
            <div class="section-title">DRIVER INFORMATION</div>
            <div class="info-grid">
                <div class="info-label">Driver Name:</div>
                <div>{{ $driver['name'] }}</div>

                <div class="info-label">Contact Number:</div>
                <div>{{ $driver['phone'] }}</div>
            </div>
        </div>
    @endif

    <!-- Items -->
    <div class="section">
        <div class="section-title">ITEMS TO DELIVER</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 40px;">#</th>
                    <th>Product Name</th>
                    <th style="width: 100px;">SKU</th>
                    <th style="width: 80px; text-align: center;">Quantity</th>
                    <th style="width: 60px; text-align: center;">Unit</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $index => $item)
                    <tr>
                        <td style="text-align: center;">{{ $index + 1 }}</td>
                        <td><strong>{{ $item['product_name'] }}</strong></td>
                        <td>{{ $item['sku'] }}</td>
                        <td style="text-align: center;"><strong>{{ $item['quantity'] }}</strong></td>
                        <td style="text-align: center;">{{ $item['unit'] }}</td>
                        <td>{{ $item['notes'] ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div style="margin-top: 10px; font-weight: bold;">
            Total Items: {{ count($items) }} | Total Quantity: {{ collect($items)->sum('quantity') }}
        </div>
    </div>

    <!-- Delivery Notes -->
    @if($delivery['notes'])
        <div class="section">
            <div class="section-title">DELIVERY NOTES</div>
            <div style="padding: 10px; background-color: #f9f9f9; border-left: 4px solid #333;">
                {{ $delivery['notes'] }}
            </div>
        </div>
    @endif

    <!-- Proof of Delivery -->
    @if($has_proof)
        <div class="section">
            <div class="section-title">PROOF OF DELIVERY</div>
            <div style="text-align: center; padding: 20px; background-color: #f9f9f9;">
                <img src="{{ $proof_image_path }}" alt="Proof of Delivery" style="max-width: 400px; max-height: 300px; border: 1px solid #ccc;">
            </div>
        </div>
    @endif

    <!-- Signature Section -->
    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-label">PREPARED BY</div>
            <div class="signature-line"></div>
            <div class="signature-text">Signature Over Printed Name</div>
            <div style="margin-top: 10px; font-size: 10px;">Date: _______________</div>
        </div>

        <div class="signature-box">
            <div class="signature-label">DELIVERED BY</div>
            <div class="signature-line"></div>
            <div class="signature-text">Driver Signature Over Printed Name</div>
            <div style="margin-top: 10px; font-size: 10px;">Date: _______________</div>
        </div>

        <div class="signature-box">
            <div class="signature-label">RECEIVED BY</div>
            <div class="signature-line"></div>
            <div class="signature-text">Customer Signature Over Printed Name</div>
            <div style="margin-top: 10px; font-size: 10px;">Date: _______________</div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>This is a computer-generated delivery receipt.</p>
        <p>Generated on {{ $generated_at }}</p>
        <p style="margin-top: 10px;">
            For concerns or inquiries, please contact us at {{ $store['phone'] }} or {{ $store['email'] }}
        </p>
    </div>
</body>
</html>
