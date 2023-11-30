<?php

namespace App\Http\Controllers;

use App\Http\Resources\TransactionResource;
use Illuminate\Http\Request;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = Transaction::orderBy('date', 'desc')->paginate(10);

        return response()->json([
            'status' => 'success',
            'message' => 'All Transactions retrieved successfully',
            'data' => $transactions,
            'last' => $transactions->lastPage(),
        ], 200); // HTTP 200 OK
    }

    public function show(string $phone_number){

        $transactions = Transaction::where('vcard', $phone_number)->orderBy('date', 'desc')->paginate(10);

        if($transactions){
            return response()->json([
            'status' => 'success',
            'message' => 'Transactions retrieved successfully',
            'data' => $transactions,
            ], 200); // HTTP 200 OK
        }

        return response()->json([
            'status' => 'error',
            'message' => 'The vcard with that phone number does not have any transactions',
        ]);
        
    }

    public function getMyTransactions() {
        $vcard = Auth::user();
        $transactions = $vcard->transactions()->orderBy('datetime', 'desc')->paginate(10);
        // $transformedTransactions = TransactionResource::collection($transactions);
        return response()->json($transactions, 200);
    }


}
