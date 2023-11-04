<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{

    public function login(Request $request){
        $user = User::where('email', $request->email)->first();
        if($user){
            if (Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status' => 'sucess',
                    'message' => [
                        'name' => $user->name,
                        'email' => $user->email,
                    ],

                ]);
            }else{
                return response()->json([
                    'status' => 'error',
                    'message' => 'Incorrect password'
                ]);
            }
        }else{
            return response()->json([
                'status' => 'error',
                'message' => 'The user does not exist'
            ]);
        }
    }

    public function index()
    {
        return User::all();
    }

    public function store(Request $request)
    {
         $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422); // HTTP 422 Unprocessable Entity
        }

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $token =  $user->createToken('API Token')->accessToken;
        $user->save();
        return response()->json([
            'status' => 'success',
            'message' => 'User registered successfully',
            'data' => [
                'name' => $user->name,
                'email' => $user->email,
                'token' => $token
            ],
        ], 201); // HTTP 201 Created
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
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
