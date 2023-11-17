<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PiggyBank;


class PiggyBankController extends Controller
{
    public function getPiggyBank()
    {
        $vcard = Auth::user();

        $piggy_bank = PiggyBank::where('vcard_phone_number', $vcard->phone_number)->first();

        return response()->json([
            'status' => 'success',
            'data' => $piggy_bank,
        ], 200);
    }
}
