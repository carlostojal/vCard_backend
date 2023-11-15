<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Vcard;
use App\Models\User;

class VCardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $vcards = Vcard::paginate(10); // Change the number (e.g., 10) to the desired items per page
        return response()->json($vcards, 200);
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|int|min:9',
            'password' => 'required',
            'email' => 'required|email',
            'confirmation_code' => 'required|min:4',
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422); // HTTP 422 Unprocessable Entity
        }

        if (!Str::startsWith($request->phone_number, '9')) {
             return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => "Phone Number needs to start with 9"
            ], 422); // HTTP 422 Unprocessable Entity

        }

        $vcard = Vcard::where('phone_number', $request->phone_number)->first();

        if(!$vcard){
            $vcard = new VCard();
            $vcard->name = $request->name;
            $vcard->phone_number = $request->phone_number;
            $vcard->email = $request->email;
            $vcard->photo_url = $request->photo_url;
            $vcard->confirmation_code = Hash::make($request->confirmation_code);
            $vcard->blocked = 0;

            //hash da pass e confirmation_code
            $vcard->password = Hash::make($request->password);
            $vcard->save();

            return response()->json([
                'status' => 'success',
                'message' => [
                    $vcard //alterar para so enviar os dados necessarios (PINIA)
                ]
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'The vcard with that phone number already exists'
        ]);
    }

    public function storeMobile(Request $request){
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|int|min:9',
            'password' => 'required|min:8',
            'confirmation_code' => 'required|min:4',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422); // HTTP 422 Unprocessable Entity
        }

        if (!Str::startsWith($request->phone_number, '9')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => "Phone Number needs to start with 9"
            ], 422); // HTTP 422 Unprocessable Entity
        }

        //This Function is for the mobile version make a register with minimum data (Phone Number, Password and Acess Code)
        $vcard = Vcard::where('phone_number', $request->phone_number)->first();
        if(!$vcard){
            $vcard = new VCard();
            $vcard->phone_number = $request->phone_number;
            $vcard->name = "name-taes"; //TAES dummydata
            $vcard->email = "email-taes";  //TAES dummydata
            $vcard->confirmation_code = Hash::make($request->confirmation_code);
            $vcard->blocked = 0;
            $vcard->balance = 0;
            $vcard->max_debit = 5000;

            //hash da pass e confirmation_code
            $vcard->password = Hash::make($request->password);
            $vcard->save();

            return response()->json([
                'status' => 'success',
                'message' => [
                    $vcard //alterar para so enviar os dados necessarios
                ]
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'The vcard with that phone number already exists'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $phone)
    {
        return Vcard::where('phone_number', $phone)->first();
    }

    public function send(Request $request){
          $validator = Validator::make($request->all(), [
            'phone_number' => 'required|int|min:9',
            'amount' => 'required|numeric',
            'confirmation_code' => 'required|min:4',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422); // HTTP 422 Unprocessable Entity
        }

        if($request->amount <= 0.00) {
            return response()->json([
                'status' => 'error',
                'message' => 'Amount needs to be greater than 0.00'
            ], 400);
        }
        $vcard_origin = Auth::user();
        if($vcard_origin->balance < $request->amount) {
            return response()->json([
                'status' => 'error',
                'message' => 'Amount needs to be lower than your balance'
            ], 400);
        }


        if(!Hash::check($request->confirmation_code, $vcard_origin->confirmation_code)) {
             return response()->json([
                'status' => 'error',
                'message' => 'Incorrect Confimation Code'
            ], 400);
        }

        $vcard_destination = Vcard::where('phone_number', $request->phone_number)->first();

        if(!$vcard_destination) {
             return response()->json([
                'status' => 'error',
                'message' => 'Phone number does not exist'
            ], 400);
        }

        if($vcard_origin == $vcard_destination){
              return response()->json([
                'status' => 'error',
                'message' => 'You cant send money to yourself'
            ], 400);
        }

        $vcard_origin->balance -= $request->amount;
        $vcard_destination->balance += $request->amount;
        $vcard_origin->save();
        $vcard_destination->save();
        return response()->json([
                'status' => 'success',
                'message' => 'Transaction Successfuly'
            ], 200);

    }

    public function profile() {

        $vcard = Auth::user();
        // dd($vcard);
        // return $vcard;
        return response()->json([
            'status' => 'success',
            'data' => $vcard,
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
