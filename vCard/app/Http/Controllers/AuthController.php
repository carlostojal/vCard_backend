<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    private function passportAuthenticationData($username, $password) {
         return [
             'grant_type' => 'password',
             'client_id' => 15,
             'client_secret' => 'ozFy7TDihpxrOkZjvkG8Y0HX0McEhk5P3GFVNwqt',
             'username' => $username,
             'password' => $password,
             'scope' => '',
         ];
    }

    public function login(Request $request) {

        $credentials = request(['email', 'name', 'phone_number', 'password']);
        if (empty($credentials['password'])) {
            return response()->json(['error' => 'Password is required'], 400);
        }

        // if(!$credentials['email'] && empty($credentials['name']) && empty($credentials['phone_number'])){
        //     return response()->json(['error' => 'Credentials are required'], 400);
        // }
        //
        if (!Auth::attempt($credentials)) {
            $credentials = request(['name', 'password']);
            if (!Auth::attempt($credentials)) {
                return response(['error' => 'Unauthorized, Wrong Credentials'], 401);
            }
        }
        $user = $request->user();

        $oauthData = $this->passportAuthenticationData($user->email, $request->password);
        $token =  $user->createToken('API Token')->accessToken;
        return response()->json([
            'status' => 'success',
            'message' => 'User Logged successfully',
            'data' => [
                'name' => $user->name,
                'email' => $user->email,
                'token' => $token
            ],
        ], 201); // HTTP 201 Created
        //
        // try {
        //     $response = Http::timeout(50)->get('http://localhost:80/api/users/');
        //     // $response = Http::timeout(5)->post('http://localhost:80/oauth/token', [
        //     //     'grant_type' => 'password',
        //     //     'client_id' => 15,
        //     //     'client_secret' => 'ozFy7TDihpxrOkZjvkG8Y0HX0McEhk5P3GFVNwqt',
        //     //     'username' => $user->email,
        //     //     'password' => $request->password,
        //     //     'scope' => '',
        //     // ]);
        //     // $request = Request::create('http://localhost:80/oauth/token', 'POST');
        //
        //     // Add the JSON data to the request body
        //     // $response = Route::dispatch($request);
        //
        //     $errorCode = $response->getStatusCode();
        //     $auth_server_response = json_decode((string) $response->content(), true);
        //     return response()->json($auth_server_response, $errorCode);
        // } catch (\Exception $e) {
        //     return response()->json('Authentication has failed! LLLLL: '. $e->getMessage(), 401);
        // }
    }
    public function logout(Request $request) {
         $accessToken = $request->user()->token();
         $token = $request->user()->tokens->find($accessToken);
         $token->revoke();
         $token->delete();
         return response(['msg' => 'Token revoked'], 200);
    }

    public function boot(): void
    {
        Passport::enableImplicitGrant();
        Passport::enablePasswordGrant();
        Passport::useClientModel(User::class);

    }

}
