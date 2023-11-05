<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    private function passportAuthenticationData($username, $password) {
         return [
             'grant_type' => 'password',
             'client_id' => '10',
             'client_secret' => "cmvHLduGdXIHf3S1HCMLm95f0BJxp64SVRpK2tn8",
             'username' => $username,
             'password' => $password,
             'scope' => ''
         ];
    }

    public function login(Request $request) {
        $credentials = request(['email', 'password']);
        if (!Auth::attempt($credentials)) {
            $credentials = request(['name', 'password']);
            if (!Auth::attempt($credentials)) {
                return response()->json(['error' => 'Unauthorized, Wrong Credentials'], 401);
            }
        }
        $user = $request->user();

        $oauthData = $this->passportAuthenticationData($user->name, $request->password);

        try {
            $request = Request::create("http://localhost:80" . '/oauth/token', 'POST', $oauthData);
            $request->headers->set('Content-Type', 'application/json');
            // $request->setContent(json_encode($oauthData));
            $response = Route::dispatch($request);
            // $response = Http::asForm()->post('http://localhost:80' . '/oauth/token', $oauthData);
            $errorCode = $response->getStatusCode();
            $auth_server_response = json_decode((string) $response->content(), true);
            return response()->json($auth_server_response, $errorCode);
        } catch (\Exception $e) {
            return response()->json('Authentication has failed!' . $e, 401);
        }
        // $token = $user->createToken('MyAppToken')->accessToken;

        // return response()->json(['token' => $token]);
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
        // Passport::enableImplicitGrant();
        Passport::enablePasswordGrant();

    }

}
