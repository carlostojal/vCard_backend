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
            return $this->errorService->sendValidatorError(422, "Form Validation Failed", $validator->errors());
        }

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();
        $token =  $user->createToken('API Token')->accessToken;

        return $this->responseService->sendWithDataResponse(201, 'User Registered Successfully', ['token' => $token]);
    }

    public function getAdmins(){

        $admins = User::where('name', 'like', 'Administrator%')->get();

        return $this->responseService->sendWithDataResponse(200, 'Admins retrieved successfully', $admins);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return $this->responseService->sendWithDataResponse(200, null, $user);
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
            return $this->errorService->sendStandardError(404, 'User not found');
        }

        $res = $user->delete();

        if($res){
            return $this->responseService->sendStandardResponse(200, 'User deleted successfully');
        }else{
            return $this->errorService->sendStandardError(500, 'User could not be deleted');
        }

    }

    public function profile(User $user){
        return $this->responseService->sendWithDataResponse(200, null, $user);
    }
}
