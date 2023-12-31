<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use DateTime;
use App\Models\TransactionPiggyBank;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PiggyBankController extends Controller
{
    public function getPiggyBank()
    {
        $vcard = Auth::user();


        // $piggy_bank = PiggyBank::where('vcard_phone_number', $vcard->phone_number)->first();
        $piggy_bank = $vcard->piggyBank;

        if(!$piggy_bank) {
            return response()->json([
            'status' => 'error',
            'message' => 'Piggy Bank not found',
        ], 422);

        }
        return response()->json([
            'status' => 'success',
            'data' => $piggy_bank,
        ], 200);
    }

    public function getTransactions() {
        $vcard = Auth::user();
        $piggy = $vcard->piggyBank;
        $transactions = $piggy->transactions()->orderBy('datetime', 'desc')->paginate(10);
        return response()->json([
            'status' => 'success',
            'data' => $transactions,
        ], 200);
    }

    public function withdraw(Request $req){
        $validator = Validator::make($req->all(), [
            'amount' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422); // HTTP 422 Unprocessable Entity
        }

        $vcard = Auth::user();
        $piggy = $vcard->piggyBank;
        if(!$piggy){
            return response()->json([
                'status' => 'error',
                'message' => 'Piggy Bank not found',
            ], 422);
        }
        if($req->amount > $piggy->balance){
            return response()->json([
                'status' => 'error',
                'message' => 'Your Piggy Bank doesnt have sufficient balance',
            ], 422);

        }

        if($req->amount <= 0.00){
            return response()->json([
                'status' => 'error',
                'message' => 'Amount need to be greater than 0.00',
            ], 422);
        }

        try {
            DB::beginTransaction();

            $newBalance = ($piggy->balance - $req->amount);
            $dt = new DateTime();
            TransactionPiggyBank::create([
                'vcard' => $vcard->phone_number,
                'date' => $dt->format('Y-m-d'),
                'datetime' => $dt->format('Y-m-d H:i:s'),
                'type' => 'WITHDRAW',
                'value' => $req->amount,
                'old_balance' => $piggy->balance,
                'new_balance' => $newBalance,
            ]);

            $vcard->balance += $req->amount;
            $vcard->save();

            $piggy->balance = $newBalance;
            $piggy->save();

            DB::commit();
        }catch(QueryException $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'status' => 'success',
            'message'=> 'Withdraw Successful',
        ], 200);

    }


     public function deposit(Request $req){
        $validator = Validator::make($req->all(), [
            'amount' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422); // HTTP 422 Unprocessable Entity
        }

        $vcard = Auth::user();
        $piggy = $vcard->piggyBank;
        if(!$piggy){
            return response()->json([
                'status' => 'error',
                'message' => 'Piggy Bank not found',
            ], 422);
        }
        if($req->amount > $vcard->balance){
            return response()->json([
                'status' => 'error',
                'message' => 'Your vCard doesnt have sufficient balance',
            ], 422);

        }

        if($req->amount <= 0.00){
            return response()->json([
                'status' => 'error',
                'message' => 'Amount need to be greater than 0.00',
            ], 422);
        }

        try {
            DB::beginTransaction();

            $newBalance = ($piggy->balance + $req->amount);
            $dt = new DateTime();
            TransactionPiggyBank::create([
                'vcard' => $vcard->phone_number,
                'date' => $dt->format('Y-m-d'),
                'datetime' => $dt->format('Y-m-d H:i:s'),
                'type' => 'DEPOSIT',
                'value' => $req->amount,
                'old_balance' => $piggy->balance,
                'new_balance' => $newBalance,
            ]);

            $vcard->balance -= $req->amount;
            $vcard->save();

            $piggy->balance = $newBalance;
            $piggy->save();

            DB::commit();
        }catch(QueryException $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'status' => 'success',
            'message'=> 'Deposit Successful',
        ], 200);

    }

}
