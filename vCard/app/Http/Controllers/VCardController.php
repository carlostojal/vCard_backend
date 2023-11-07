<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vcard;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class VCardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }


    public function calculate_password(Request $request){
        
        //nao sei bem se é a junção com a password do user
        $user_password = User::where('email', $request->email)->first()->password;
        $phone_number = $request->phone_number;
        $password = Hash::make($user_password . $phone_number);

        return $password;
    }


    public function store(Request $request)
    {
        $vcard = Vcard::where('phone_number', $request->phone_number)->first();
        
        if(!$vcard){
            $vcard = new VCard();
            $vcard->name = $request->name;
            $vcard->phone_number = $request->phone_number;
            $vcard->email = $request->email;
            if($request->photo_url)
                $vcard->photo_url = $request->photo_url;
            $vcard->confirmation_code = $request->confirmation_code;
            $vcard->blocked = 0;
            $vcard->balance = 0;
            $vcard->max_debit = $request->max_debit;

            //hash da pass e confirmation_code
            $vcard->password = $this->calculate_password($request);

            //$vcard->save();

            return response()->json([
                'status' => 'sucess',
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

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
