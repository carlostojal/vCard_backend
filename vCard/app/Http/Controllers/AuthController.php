<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Vcard;
use App\Services\ErrorService;
use App\Services\ResponseService;

class AuthController extends Controller
{
    protected $errorService;
    protected $responseService;

    public function __construct()
    {
        $this->errorService = new ErrorService();
        $this->responseService = new ResponseService();
    }
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
            return $this->responseService->sendStandardResponse(200, 'users');
        } elseif (Auth::guard('vcard')->check()) {
            return $this->responseService->sendStandardResponse(200, 'vcards');
        }
        return $this->errorService->sendStandardError(500, 'User not logged');
    }

    public function loginVcard(Request $request){
        //This Login is for vCard users from both TAES and DAD
        $validator = Validator::make($request->all(), [
            'phone_number' => 'regex:/^(?:\+351)?9\d{7,}$/',
            'password' => 'required|min:3',
        ]);

        if ($validator->fails()) {
            return $this->errorService->sendValidatorError(422, "Form Validation Failed", $validator->errors());
        }

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

        if($vcard->blocked == 1){
            return $this->errorService->sendStandardError(422, "vCard is blocked");
        }

        if(Hash::check($request->password, $vcard->password)){
            $oauthData = $this->AddAuthDataVcard($request->phone_number, $request->password);
            request()->request->add($oauthData);

            $request = Request::create('http://localhost:80/oauth/token', 'POST');
            $response = Route::dispatch($request);
            $errorCode = $response->getStatusCode();
            if ($errorCode != 200) {
                return $this->responseService->sendStandardResponse(500, 'Not able to authenticate, token was not able to be produced');
            }

            $responseData = json_decode($response->getContent(), true);
            return $this->responseService->sendWithDataResponse(200, 'vCard User Logged successfully', $responseData);
        }
        return $this->errorService->sendStandardError(401, 'Wrong Credentials');
    }

    public function loginUser(Request $request){
        //This Login is only for Admins users from DAD
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:3',
        ]);

        if ($validator->fails()) {
            return $this->errorService->sendValidatorError(422, "Form Validation Failed", $validator->errors());
        }

        $credentials = $request->only('email', 'password');

        if (auth()->guard('web')->attempt($credentials)) {
            $user = auth()->guard('web')->user();

            $oauthData = $this->AddAuthDataUser($request->email, $request->password);
            request()->request->add($oauthData);

            $request = Request::create('http://localhost:80/oauth/token', 'POST');
            $response = Route::dispatch($request);
            $errorCode = $response->getStatusCode();

            if ($errorCode != 200) {
                return $this->responseService->sendStandardResponse(500, 'Not able to authenticate, token was not able to be produced');
            }

            $responseData = json_decode($response->getContent(), true);
            return $this->responseService->sendWithDataResponse(200, 'User Logged Successfully', $responseData);
        }
        return $this->errorService->sendStandardError(401, 'Wrong Credentials');
    }


    public function logout(Request $request) {
         $user = Auth::user();
         $token = $user->token();
         if(!$token){
            $this->errorService->sendStandardError(500, 'Token was already revoked');

         }
         $token->revoke();
         $token->delete();
         return $this->responseService->sendStandardResponse(200, 'Token was revoked form user '.$user->name);
    }

    public function boot(): void
    {
        Passport::enableImplicitGrant();
        Passport::enablePasswordGrant();
        Passport::useClientModel(User::class);
    }

}
