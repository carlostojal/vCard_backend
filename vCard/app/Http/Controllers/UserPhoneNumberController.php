<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserPhoneNumber;

class UserPhoneNumberController extends Controller
{
    public function index()
    {
        return UserPhoneNumber::all();
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|integer',
            'phone_number' => 'required|string|max:15',
        ]);

        return UserPhoneNumber::create($validatedData);
    }

    public function show(UserPhoneNumber $userPhoneNumber)
    {
        return $userPhoneNumber;
    }

    public function update(Request $request, UserPhoneNumber $userPhoneNumber)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|integer',
            'phone_number' => 'required|string|max:15',
        ]);

        $userPhoneNumber->update($validatedData);

        return $userPhoneNumber;
    }

    public function destroy(UserPhoneNumber $userPhoneNumber)
    {
        $userPhoneNumber->delete();

        return response()->json(['message' => 'Record deleted'], 204);
    }
}
