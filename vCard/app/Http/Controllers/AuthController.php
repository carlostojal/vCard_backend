<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    private function passportAuthenticationData($username, $password) {
         return [
             'grant_type' => 'password',
             'client_id' => 5,
             'client_secret' => "5W7Ql1WKY9QMpWqXpAJaHJNcsAZT3Tyz6rc6J9q9",
             'name' => $username,
             'password' => $password,
             'scope' => ''
         ];
    }

    public function login(Request $request) {
         $validator = Validator::make($request->all(), [
            'name' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid input data'], 400);
        }

        try {
            request()->request->add($this->passportAuthenticationData($request->name, $request->password));
            $request = Request::create("http://localhost:80". '/oauth/token', 'POST');
            $response = Route::dispatch($request);
            $errorCode = $response->getStatusCode();
            $auth_server_response = json_decode((string) $response->content(), true);
            return response()->json($auth_server_response, $errorCode);
            }
        catch (\Exception $e) {
            return response()->json('Authentication has failed!', 401);
        }
    }

    public function logout(Request $request) {
         $accessToken = $request->user()->token();
         $token = $request->user()->tokens->find($accessToken);
         $token->revoke();
         $token->delete();
         return response(['msg' => 'Token revoked'], 200);
    }

}
