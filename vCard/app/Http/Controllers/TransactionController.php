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
        $transactions = Transaction::paginate(10);
        return response()->json($transactions, 200);
    }

    public function getMyTransactions() {
        $vcard = Auth::user();
        $transactions = $vcard->transactions()->paginate(10);
        $transactions = $transactions->collect()->mapInto(TransactionResource::class)->toArray();
        return response()->json($transactions, 200);
    }


}
