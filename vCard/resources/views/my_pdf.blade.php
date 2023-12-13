<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Styled Table</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }

        th {
            background-color: #f2f2f2;
        }

        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        hr {
            border: 0;
            border-top: 1px solid #dddddd;
            margin: 5px 0;
        }
        h1 {
            font-size: 28px;
            color: #333;
            margin-bottom: 10px;
        }

        h2 {
            font-size: 20px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>

<h1>Transactions</h1>

<h2>My Month: {{ $month }}</h2>

<table>
    <thead>
        <tr>
            <th>Date</th>
            <th>Description</th>
            <th>Amount</th>
            <th>Balance</th>
            <th>Type</th>
            <th>Payment Type</th>
            <th>Recipient</th>
        </tr>
    </thead>
    <tbody>
        @foreach($transactions as $transaction)
            <tr>
                <td>{{ $transaction['date'] }}</td>
                <td>{{ $transaction['description'] }}</td>
                <td>€{{ number_format($transaction['value'], 2) }}</td>
                <td>€{{ number_format($transaction['new_balance'], 2) }}</td>
                @if($transaction['type'] == 'C')
                    <td>Credit</td>
                @else
                    <td>Debit</td>
                @endif
                <td>{{ $transaction['payment_type'] }}</td>
                <td>{{ $transaction['payment_reference'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>
