<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Details</title>
</head>
<body>
    <h1>Payment Receipt</h1>
    <table border="1" cellpadding="5" cellspacing="0">
        <tr>
            <th>Order ID</th>
            <th>Payment Method</th>
            <th>Payment Status</th>
            <th>Transaction ID</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Country</th>
            <th>Street Address</th>
            <th>Town/City</th>
            <th>State/County</th>
            <th>Postcode</th>
            <th>Phone</th>
            <th>Email</th>
            <th>Order Notes</th>
        </tr>
        <tr>
            <td>{{ $payment->order_id }}</td>
            <td>{{ $payment->payment_method }}</td>
            <td>{{ $payment->payment_status }}</td>
            <td>{{ $payment->transaction_id }}</td>
            <td>{{ $payment->first_name }}</td>
            <td>{{ $payment->last_name }}</td>
            <td>{{ $payment->country }}</td>
            <td>{{ $payment->street_address }}</td>
            <td>{{ $payment->town_city }}</td>
            <td>{{ $payment->state_county }}</td>
            <td>{{ $payment->postcode }}</td>
            <td>{{ $payment->phone }}</td>
            <td>{{ $payment->email }}</td>
            <td>{{ $payment->order_notes }}</td>
        </tr>
    </table>
</body>
</html>
