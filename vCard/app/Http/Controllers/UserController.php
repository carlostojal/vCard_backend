<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

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
        $user = User::where('email', $request->email)->first();
        if(!$user){
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;

            $user->password = Hash::make($request->password);
            $user->save();

            return response()->json([
                'status' => 'sucess',
                'message' => [
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'The user already exists'
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