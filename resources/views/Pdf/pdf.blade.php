<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
            max-width: 1000px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;

            box-sizing: border-box;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eee;
        }

        .logo {
            max-width: 200px;
            height: auto;
        }

        .details-info {
            font-size: 14px;
            color: #666;
            margin-bottom: 20px;
        }

        .details-info p {
            margin: 5px 0;
        }

        .info-section {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

       

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }


        .summary-container {
            margin-top: 20px;
            display: flex;
            justify-content: flex-end;
        }

        .summary-main {
            width: 250px;
        }

        .summary-heading {
            font-size: 16px;
            margin-bottom: 10px;
            color: #333;
        }

        .summary-card {
            border: 1px solid #ddd;
            padding: 15px;
            background-color: #f9f9f9;
        }

        .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .truncate {


            font-size: 13px;
            max-width: 150px;
        }

        @media (max-width: 600px) {

            .bill-to,
            .invoice-info {
                flex-basis: 100%;
            }

            table {
                font-size: 12px;
            }

            th,
            td {
                padding: 6px;
            }

            .truncate {
                max-width: 150px;
            }

            .summary-main {
                width: 100%;
            }
        }
    </style>
</head>

{{--
@php
$order_histories = [
'details' => [
'id' => 1,
'header_logo' => 'uploads/global/logo2.png',
'footer_logo' => 'uploads/global/pulse-whitelogo.png',
'address' => '9119 Church St. Manassas, VA 20110',
'phone_number' => '(800) 700-1357',
'trade_mark' => 'Pulse Experiential Travel © 2011-2024',
'term_policy' => 'Terms & conditions | Privacy Policy',
'first_name' => 'testing',
'email' => 'aviralgit@gmail.com',
],
'transaction_data' => [
[
'transaction_id' => 'tnx_66bf5ccf26b4f8056',
'created_at' => '2024-08-16 14:06:13',
'total_price' => '4,000.00',
'data' => [
[
'id' => 43,
'product_title' => 'March Madness - The NCAA Basketball Tournament',
'sku' => 'MARCHMD3N24',
'quantity' => 1,
'retail_price' => '4000.00',
'ticket_id' => null,
'hotel_id' => null,
'merchandise_description' => null,
'type_of_payment' => 'visa',
'total_price' => '4000.00',
'last_four_digit' => '4242',
'product_id' => null,
'invoice' => '9871490',
'certificate_code' => 'MARCHMD3N24-081624-TST-W1U',
],
],
],
],
];
@endphp --}}


<body>
    <div class="container">
        <div class="header">
            <img src="{{ $order_histories['details']['header_logo'] }}" alt="Header Logo" class="logo">
        </div>

        <div class="details-info">
            <p>{{ $order_histories['details']['address'] }}</p>
            <p>{{ $order_histories['details']['phone_number'] }}</p>
            <p>{{ $order_histories['details']['trade_mark'] }}</p>
            <p>{{ $order_histories['details']['term_policy'] }}</p>
        </div>

        <div class="info-section">
            <div class="bill-to">
                <h4>Bill To:</h4>
                <p><strong>Name:</strong> {{ $order_histories['details']['first_name'] }}</p>
                <p><strong>Email:</strong> {{ $order_histories['details']['email'] }}</p>
            </div>

            <div class="invoice-info">
                <p><strong>Invoice:</strong> #{{ $order_histories['transaction_data'][0]['data'][0]['invoice'] }}</p>
                <p><strong>Date:</strong> {{ $order_histories['transaction_data'][0]['created_at'] }}</p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Qty</th>
                    <th>SKU</th>
                    <th>Product Title</th>
                    <th>Certificate Code</th>
                    <th>Unit Cost</th>
                    <th>Total Cost</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order_histories['transaction_data'][0]['data'] as $item)
                <tr>
                    <td>{{ $item['quantity'] }}</td>
                    <td>{{ $item['sku'] }}</td>
                    <td class="truncate">{{ $item['product_title'] }}</td>
                    <td class="truncate">{{ $item['certificate_code'] }}</td>
                    <td>${{ number_format($item['retail_price'], 2) }}</td>
                    <td>${{ number_format($item['total_price'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary-container">
            <div class="summary-main">
                <h5 class="summary-heading">Order Summary</h5>
                <div class="summary-card">
                    <div class="row">
                        <span>Total Items:</span>
                        <span>{{ count($order_histories['transaction_data'][0]['data']) }}</span>
                    </div>
                    <div class="row">
                        <span>Total Price:</span>
                        <span><strong>${{ $order_histories['transaction_data'][0]['total_price'] }}</strong></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
