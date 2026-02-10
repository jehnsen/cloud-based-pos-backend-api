<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>@yield('title')</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .store-name {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .report-title {
            font-size: 16px;
            font-weight: bold;
            margin-top: 10px;
        }
        .report-date {
            font-size: 11px;
            color: #666;
            margin-top: 5px;
        }
        .summary-box {
            background-color: #f5f5f5;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .summary-label {
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background-color: #333;
            color: white;
            padding: 10px;
            text-align: left;
            font-weight: bold;
        }
        td {
            padding: 8px;
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
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        .total-row {
            font-weight: bold;
            background-color: #e8e8e8;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="store-name">{{ $store->name }}</div>
        <div>{{ $store->address ?? '' }}</div>
        <div class="report-title">@yield('report-title')</div>
        <div class="report-date">Generated on: {{ now()->format('F d, Y h:i A') }}</div>
    </div>

    @yield('content')

    <div class="footer">
        <p>This is a computer-generated report. No signature required.</p>
    </div>
</body>
</html>
