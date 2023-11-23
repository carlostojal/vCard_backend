<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Vcard;


class AuthController extends Controller
{

    // trim the country code from the phone number string, in case it is provided
    private function trimPortugueseCountryCode($phoneNumber) {
        if (strpos($phoneNumber, '+351') === 0) {
        $phoneNumber = substr($phoneNumber, 4);
        }
        return $phoneNumber;
    }

    private function AddAuthDataVCard($phone, $password){
        return [
            'grant_type' => 'password',
            'client_id' => env('VCARD_CLIENT_ID'),
            'client_secret' => env('VCARD_CLIENT_SECRET'),
            'username' => $phone,
            'password' => $password,
            'scope' => '',
         ];
    }


    private function AddAuthDataUser($email, $password){
        return [
             'grant_type' => 'password',
             'client_id' => env('USER_CLIENT_ID'),
             'client_secret' => env('USER_CLIENT_SECRET'),
             'username' => $email,
             'password' => $password,
             'scope' => '',
         ];
    }

    public function getAuthenticatedGuard(){
        if (Auth::guard('api')->check()) {
            return response()->json(['status' => 'success', 'message' => 'users']);
        } elseif (Auth::guard('vcard')->check()) {
            return response()->json(['status' => 'success', 'message' => 'vcards']);
        }

        return response()->json(['status' => 'error', 'message' => 'User not logged']);
    }

    public function loginVcard(Request $request){
        //This Login is for vCard users from both TAES and DAD
        $validator = Validator::make($request->all(), [
            'phone_number' => 'regex:/^(?:\+351)?9\d{7,}$/',
            'password' => 'required|min:3',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Form Validation failed',
                'errors' => $validator->errors(),
            ], 422); // HTTP 422 Unprocessable Entity
        }

        // trim the input phone number
        $request->phone_number = $this->trimPortugueseCountryCode($request->phone_number);

        $credentials = request(['phone_number', 'password']);
        $vcard = Vcard::where('phone_number', $request->phone_number)->first();
        if(!$vcard) {
            return response()->json([
                'status' => 'error',
                'message' => 'Login failed',
                'errors' => 'Phone Number Not found',
            ], 404);
        }
        if(Hash::check($request->password, $vcard->password)){

            $oauthData = $this->AddAuthDataVcard($request->phone_number, $request->password);
            request()->request->add($oauthData);

            $request = Request::create('http://localhost:80/oauth/token', 'POST');
            $response = Route::dispatch($request);
            $errorCode = $response->getStatusCode();
            if ($errorCode != 200) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Not able to authenticate, token was not able to be produced',
                ], 201);
            }

            $responseData = json_decode($response->getContent(), true);
            $token = $responseData;
            return response()->json([
                'status' => 'success',
                'message' => 'vCard User Logged successfully',
                'data' => $responseData,
            ], 201);
        }

         return response()->json([
            'status' => 'error',
            'message' => 'Wrong Credentials'
        ], 401);
    }

    public function loginUser(Request $request){
        //This Login is only for Admins users from DAD
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:3',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422); // HTTP 422 Unprocessable Entity
        }

        $credentials = $request->only('email', 'password');
        //$user = User::find($request->email);
        // $user = User::where('email', $request->email)->first();

        if (auth()->guard('web')->attempt($credentials)) {
            $user = auth()->guard('web')->user();

            $oauthData = $this->AddAuthDataUser($request->email, $request->password);
            request()->request->add($oauthData);

            $request = Request::create('http://localhost:80/oauth/token', 'POST');
            $response = Route::dispatch($request);
            $errorCode = $response->getStatusCode();

            if ($errorCode != 200) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Not able to authenticate, token was not able to be produced',
                ], 201);
            }

            $responseData = json_decode($response->getContent(), true);
            $token = $responseData;
            return response()->json([
                'status' => 'success',
                'message' => 'User Logged successfully',
                'data' => [
                    $responseData
                ],
            ], 201);
        }
        // return response(['error' => 'Unauthorized, Wrong Credentials'], 401);
        return response()->json([
            'status' => 'error',
            'message' => 'Wrong Credentials'
        ], 401);

    }


    public function logout(Request $request) {
         $user = Auth::user();
         $token = $user->token();
         if(!$token){
            return response()->json([
                'status' => 'error',
                'message' => 'Token was already revoked'
            ], 200);

         }
         // $token = $user->tokens->find($accessToken);
         $token->revoke();
         $token->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Token was revoked from user '.$user->name
        ], 200);
    }

    public function boot(): void
    {
        Passport::enableImplicitGrant();
        Passport::enablePasswordGrant();
        Passport::useClientModel(User::class);
    }

}
