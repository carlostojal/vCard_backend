<!DOCTYPE html>
<html>
<head>
    <title>Bank Transaction Extract</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        h1 {
            color: #336699;
        }
        .transaction-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .transaction-table, .transaction-table th, .transaction-table td {
            border: 1px solid #ddd;
        }
        .transaction-table th, .transaction-table td {
            padding: 10px;
            text-align: left;
        }
        .transaction-table th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>Bank Transaction Extract</h1>
    <p>Month: {{ $month }}</p>

    <table class="transaction-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Description</th>
                <th>Amount</th>
                <th>Old Balance</th>
                <th>New Balance</th>
                <th>Type</th>
                <th>Payment Type</th>
                <th>Category</th>
                <th>Pair Vcard</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $transaction)
                <tr>
                    <td>{{ $transaction['date'] }}</td>
                    <td>{{ $transaction['description'] }}</td>
                    <td>${{ number_format($transaction['amount'], 2) }}</td>
                    <td>${{ number_format($transaction['old_balance'], 2) }}</td>
                    <td>${{ number_format($transaction['new_balance'], 2) }}</td>
                    <td>{{ $transaction['type'] }}</td>
                    <td>{{ $transaction['payment_type'] }}</td>
                    <td>{{ $transaction['category'] }}</td> 
                    <td>{{ $transaction['pair_vcard'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
