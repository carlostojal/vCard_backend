<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\DefaultCategory;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use DateTime;
use App\Models\Transaction;
use App\Models\Vcard;
use App\Models\User;
use App\Models\PiggyBank;
use App\Services\ErrorService;
use App\Services\ResponseService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class VCardController extends Controller
{

    protected $errorService;
    protected $responseService;

    public function __construct(){
        $this->errorService = new ErrorService();
        $this->responseService = new ResponseService();

    }

    public function index()
    {

        $vcards = Vcard::paginate(10);

        return response()->json([
            $vcards,
            'last' => $vcards->lastPage(),
        ], 200); // HTTP 200 OK
    }


    public function indexBlocked(Request $request){

        $validator = Validator::make($request->all(), [
            'blocked' => 'required|in:all,0,1',
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422); // HTTP 422 Unprocessable Entity
        }

        if($request->blocked != 'all'){
            $vcards = Vcard::where('blocked', $request->blocked)->paginate(10);
        }else{
            $vcards = Vcard::paginate(10);
        }

        return response()->json([
            $vcards,
            'last' => $vcards->lastPage(),
        ], 200); // HTTP 200 OK

    }


    // trim the country code from the phone number string, in case it is provided
    private function trimPortugueseCountryCode($phoneNumber)
    {
        if (strpos($phoneNumber, '+351') === 0) {
            $phoneNumber = substr($phoneNumber, 4);
        }
        return $phoneNumber;
    }


    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'phone_number' => 'regex:/^(?:\+351)?9[1236]\d{7}$/',
            'password' => 'required',
            'email' => 'required|email',
            'confirmation_code' => 'required|min:3',
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);         }

        $request->phone_number = $this->trimPortugueseCountryCode($request->phone_number);

        $vcard = Vcard::where('phone_number', $request->phone_number)->first();

        if (!$vcard) {
            $vcard = new VCard();
            $vcard->name = $request->name;
            $vcard->phone_number = $request->phone_number;
            $vcard->email = $request->email;
            //$vcard->photo_url = $request->photo_url;
            if($request->hasFile('photo')){
                $photo = $request->file('photo');
                $photoName = $request->phone_number . "_" . time() . '.' . $photo->extension();
                $photo->storeAs('fotos',$photoName,'public');
                $vcard->photo_url = $photoName;
            }
            $vcard->confirmation_code = Hash::make($request->confirmation_code);
            $vcard->blocked = 0;

            $vcard->password = Hash::make($request->password);
            $vcard->save();

            $allCats = DefaultCategory::all();
            foreach($allCats as $dCat){
                $cat = Category::create([
                    'vcard' => $request->phone_number,
                    'type' => $dCat->type,
                    'name' => $dCat->name
                ]);
                $cat->save();
            }

            return response()->json([
                'status' => 'success',
                'message' => [
                    $vcard
                ]
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'The vcard with that phone number already exists'
        ]);
    }


    public function storeMobile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|int|min:9',
            'password' => 'required|min:8',
            'confirmation_code' => 'required|min:3',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422); // HTTP 422 Unprocessable Entity
        }

        if (!Str::startsWith($request->phone_number, '9')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => "Phone Number needs to start with 9"
            ], 422); // HTTP 422 Unprocessable Entity
        }

        //This Function is for the mobile version make a register with minimum data (Phone Number, Password and Acess Code)
        $vcard = Vcard::where('phone_number', $request->phone_number)->first();
        if (!$vcard) {
            $vcard = new VCard();
            $vcard->phone_number = $request->phone_number;
            $vcard->name = "name-taes"; //TAES dummydata
            $vcard->email = "email-taes";  //TAES dummydata
            $vcard->confirmation_code = Hash::make($request->confirmation_code);
            $vcard->blocked = 0;
            $vcard->balance = 0;
            $vcard->max_debit = 5000;
            $vcard->password = Hash::make($request->password); //hash da pass e confirmation_code
            $vcard->save();

            $piggy_bank = new PiggyBank();
            $piggy_bank->balance = 0;
            $piggy_bank->vcard_phone_number = $request->phone_number;
            $piggy_bank->save();

            return response()->json([
                'status' => 'success',
                'message' => [
                    $vcard, //alterar para so enviar os dados necessarios
                    $piggy_bank
                ]
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'The vcard with that phone number already exists'
        ]);
    }


    public function show(string $query, Request $request)
    {

        $validator = Validator::make($request->all(), [
            'blocked' => 'required|in:all,0,1',
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422); // HTTP 422 Unprocessable Entity
        }

        switch ($query) {
            case Str::startsWith($query, '9') && strlen($query) == 9 && is_numeric($query):
                $vcards = Vcard::where('phone_number', $query);
                break;
            case Str::contains($query, '@'):
                $vcards = Vcard::where('email', $query);
                break;
            default:
                $vcards = Vcard::where('name', 'LIKE', '%' . $query . '%');
                break;
        }

        //Get the query allready filtered by name or phone or email and filter by blocked
        if($request->blocked != 'all'){
            $vcards = $vcards->where('blocked', $request->blocked)->paginate(10);
        }else{
            $vcards = $vcards->paginate(10);
        }

        if($vcards){
            return response()->json([
                'status' => 'success',
                'message' => 'vcard retrieved successfully',
                'data' => $vcards,
                'last' => $vcards->lastPage(),
            ], 200); // HTTP 200 OK
        }

        return response()->json([
            'status' => 'error',
            'message' => 'The vcard with that phone number does not exist',
        ]);
    }

    public function deleteVcard(string $phone_number)
    {
        $validare = Validator::make(['phone_number' => $phone_number], [
            'phone_number' => 'required|min:9',
        ]);

        if($validare->fails()){
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validare->errors(),
            ], 422); // HTTP 422 Unprocessable Entity
        }

        $vcard = Vcard::where('phone_number', $phone_number)->first();

        if ($vcard) {

            $transactions = Transaction::where('vcard', $phone_number)->orWhere('pair_vcard', $phone_number)->get();

            //se tiver o balance a 0 e tiver transactions
            if($vcard->balance == 0 && $transactions->count() > 0){
                $vcard->delete();
            }else{
                $vcard->forceDelete();
            }

            return response()->json([
                'status' => 'success',
                'message' => 'vcard deleted successfully',
            ], 200); // HTTP 200 OK

        }

        return response()->json([
            'status' => 'error',
            'message' => 'The vcard with that phone number does not exist',
        ]);
    }


    public function profile()
    {
        $vcard = Auth::user();
        return $this->responseService->sendWithDataResponse(200, null, $vcard);
    }


    public function getBalance(){
        $vcard = Auth::user();
        return $this->responseService->sendWithDataResponse(200, null, $vcard->balance);
    }


    public function send(Request $request) //Transfer money to another vcard
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|min:9',
            'description' => '',
            'amount' => 'required|numeric',
            'confirmation_code' => 'required|min:3|max:4',
            'payment_type' => ['required', 'in:VCARD,MBWAY,PAYPAL,IBAN,MB,VISA'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422); // HTTP 422 Unprocessable Entity
        }

        if ($request->amount <= 0.00) {
            return response()->json([
                'status' => 'error',
                'message' => 'Amount needs to be greater than 0.00'
            ], 400);
        }
        $vcard_origin = Auth::user();
        if ($vcard_origin->balance < $request->amount) {
            return response()->json([
                'status' => 'error',
                'message' => 'Amount needs to be lower than your balance'
            ], 400);
        }

        if ($vcard_origin->max_debit < $request->amount) {
            return response()->json([
                'status' => 'error',
                'message' => 'Amount needs to be lower than your max debit limit'
            ], 400);
        }

        if (!Hash::check($request->confirmation_code, $vcard_origin->confirmation_code)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Incorrect Confimation Code'
            ], 400);
        }

        $vcard_destination = Vcard::where('phone_number', $request->phone_number)->first();

        if (!$vcard_destination) {
            return response()->json([
                'status' => 'error',
                'message' => 'Phone number does not exist'
            ], 400);
        }

        if ($vcard_origin->phone_number == $vcard_destination->phone_number) {
            return response()->json([
                'status' => 'error',
                'message' => 'You cant send money to yourself'
            ], 400);
        }

        //There are a lot of payment types so each one should follow a different logic
        switch ($request->payment_type) {
            case "VCARD":
                $this->makeVCARDTransaction($vcard_origin, $vcard_destination, $request);
                break;
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Transaction Successfuly'
        ], 200);
    }


    private function makeVCARDTransaction($vcard, $vcard2, $request)
    {
        $newBalance = $vcard->balance - $request->amount;
        $newBalance2 = $vcard2->balance + $request->amount;

        $trans = new Transaction();
        $trans2 = new Transaction();

        $trans->vcard = $vcard->phone_number;
        $dt = new DateTime();
        $trans->date = $dt->format('Y-m-d');
        $trans->datetime = $dt->format('Y-m-d H:i:s');
        $trans->type = 'D';
        $trans->value = $request->amount;
        $trans->old_balance = $vcard->balance;
        $trans->new_balance = $newBalance;
        $trans->payment_type = "VCARD";
        if($request->description) $trans->description = $request->description;
        $trans->pair_vcard = $vcard2->phone_number;
        $trans->payment_reference = $vcard2->phone_number;

        $trans2->vcard = $vcard2->phone_number;
        $trans2->date = $dt->format('Y-m-d');
        $trans2->datetime = $dt->format('Y-m-d H:i:s');
        $trans2->type = 'C';
        $trans2->value = $request->amount;
        $trans2->old_balance = $vcard2->balance;
        $trans2->new_balance = $newBalance2;
        $trans2->payment_type = "VCARD";
        $trans2->pair_vcard = $vcard->phone_number;
        if($request->description) $trans2->description = $request->description;
        $trans->payment_reference = $vcard2->phone_number;
        $trans2->payment_reference = $vcard->phone_number;

        $trans->save();
        $trans2->save();

        $trans->pair_transaction = $trans2->id;
        $trans2->pair_transaction = $trans->id;

        $vcard->balance = $newBalance;
        $vcard2->balance = $newBalance2;

        $vcard->save();
        $vcard2->save();

        $trans->save();
        $trans2->save();

    }

    public function changeBlock(String $phone_number, Request $request){

        //validar os inputs
        $validator = Validator::make(['phone_number' => $phone_number, 'block' => $request->block], [
            'phone_number' => 'required|min:9',
            'block' => 'required|in:0,1',
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422); // HTTP 422 Unprocessable Entity
        }

        $vcard = Vcard::where('phone_number', $phone_number)->first();

        if($vcard){

            if($vcard->blocked == $request->block){
                return response()->json([
                    'status' => 'error',
                    'message' => 'The vcard is already blocked/unblocked',
                ], 400);
            }

            $vcard->blocked = $request->block;
            $vcard->save();
            return response()->json([
                'status' => 'success',
                'message' => 'vcard blocked successfully',
                'data' => $vcard,
            ], 200); // HTTP 200 OK
        }

        return response()->json([
            'status' => 'error',
            'message' => 'The vcard with that phone number does not exist',
        ]);

    }


    public function deleteVcardMobile(){

        $vcard = Auth::user();

        if($vcard){

            //$vcard->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'vcard deleted successfully',
            ], 200); // HTTP 200 OK
        }

        return response()->json([
            'status' => 'error',
            'message' => 'The vcard does not exist',
        ]);

    }


    public function updateMaxDebit(Request $request, $id){

        $validator = Validator::make(['max_debit' => $request->max_debit], [
            'max_debit' => 'required|numeric|min:0',
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422); // HTTP 422 Unprocessable Entity
        }

        $vcard = Vcard::where('phone_number', $id)->first();

        if($vcard){
            $vcard->max_debit = $request->max_debit;
            $vcard->save();
        }else{
            return response()->json([
                'status' => 'error',
                'message' => 'The vcard with that phone number does not exist',
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'vcard updated successfully',
            'data' => $vcard,
        ], 200); // HTTP 200 OK

    }

    public function getPhotoUrl(){
        $vcard = Auth::user();
        if(Storage::exists("public/fotos/".$vcard->photo_url)){
            $url = Storage::url("fotos/".$vcard->photo_url);
            return $this->responseService->sendWithDataResponse(200, null, ['photo' => $url]);
        }
        return $this->errorService->sendStandardError(404, "File not found");
    }
}
