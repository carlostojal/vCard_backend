<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Services\ErrorService;
use App\Services\ResponseService;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{

    protected $errorService;
    protected $responseService;

    public function __construct()
    {
        $this->errorService = new ErrorService();
        $this->responseService = new ResponseService();
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
        $user->save();
        $token =  $user->createToken('API Token')->accessToken;
        return response()->json([
            'status' => 'success',
            'message' => 'User registered successfully',
            'data' => [
                // 'name' => $user->name,
                // 'email' => $user->email,
                'token' => $token
            ],
        ], 201); // HTTP 201 Created
    }

    public function getAdmins(){

        $admins = User::where('name', 'like', 'Administrator%')->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Admins retrieved successfully',
            'data' => $admins,
        ], 200); // HTTP 200 OK
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
        $user = User::where('id', $id)->first();

        if($user == null){
            return response()->json([
                'status' => 'error',
                'message' => 'User not found',
            ], 404); // HTTP 404 Not Found
        }

        $res = $user->delete();

        if($res){
            return response()->json([
                'status' => 'success',
                'message' => 'User deleted successfully',
            ], 200); // HTTP 200 OK
        }else{
            return response()->json([
                'status' => 'error',
                'message' => 'User could not be deleted',
            ], 500); // HTTP 500 Internal Server Error
        }

    }

    public function profile(){
        $user = Auth::user();
        return $this->responseService->sendWithDataResponse(200, null, $user);
    }
}
