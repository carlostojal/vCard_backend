<?php

namespace App\Http\Controllers;

use App\Http\Resources\TransactionResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Vcard;
use App\Models\User;
use App\Services\ErrorService;
use App\Services\ResponseService;

class TransactionController extends Controller
{

    protected $errorService;
    protected $responseService;

    public function __construct(){
        $this->errorService = new ErrorService();
        $this->responseService = new ResponseService();

    }

    public function index()
    {
        $transactions = Transaction::orderBy('date', 'desc')->paginate(10);

        // return response()->json([
        //     'status' => 'success',
        //     'message' => 'All Transactions retrieved successfully',
        //     'data' => $transactions,
        //     'last' => $transactions->lastPage(),
        // ], 200); // HTTP 200 OK
        return $this->responseService->sendWithDataResponse(200, "All Transactions retrieved successfully", ['transactions' => $transactions, 'last' => $transactions->lastPage()]);
    }

    public function show(string $query, Request $request){

        $validator = Validator::make($request->all(), [
            'type' => 'required|in:all,D,C',
        ]);

        if($validator->fails()){
            return $this->errorService->sendValidatorError(422, "Validation Failed", $validator->errors());
        }

        switch ($query){
            case Str::startsWith($query, '9') && strlen($query) == 9 && is_numeric($query): //phone number
                $transactions = Transaction::where('vcard', $query)->orderBy('datetime', 'desc');
                break;
            case Str::contains($query, '@'): //email
                $phone = Vcard::where('email', $query)->select('phone_number');
                $transactions = Transaction::where('vcard', $phone)->orderBy('datetime', 'desc');

                break;
            default: //name
                $phone = Vcard::where('name', 'LIKE', '%' . $query . '%')->pluck('phone_number');
                $transactions = Transaction::whereIn('vcard', $phone)->orderBy('datetime', 'desc');
                break;
        }


        //Get the query allready filtered by name or phone or email and filter by blocked
        if($request->type != 'all'){
            $transactions = $transactions->where('type', $request->type)->paginate(10);
        }else{
            $transactions = $transactions->paginate(10);
        }

        if($transactions){
            // return response()->json([
            // 'status' => 'success',
            // 'message' => 'Transactions retrieved successfully',
            // 'data' => $transactions,
            // 'last' => $transactions->lastPage(),
            // ], 200); // HTTP 200 OK
            return $this->responseService->sendWithDataResponse(200, "Transactions retrieved successfully", ['transactions' => $transactions, 'last' => $transactions->lastPage()]);
        }
        return $this->errorService->sendStandardError(404, "The vcard with that phone number does not have any transactions");

    }

    /*public function MyTransactionsType(Request $request){

        $validator = Validator::make($request->all(), [
            'type' => 'required|in:all,D,C',
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422); // HTTP 422 Unprocessable Entity
        }

        $vcard = Auth::user();

        if($request->type != 'all'){
            $transactions = $vcard->transactions()->where('type', $request->type)->orderBy('datetime', 'desc')->paginate(10);
            //$transactions = Transaction::where('type', $request->type)->orderBy('datetime', 'desc')->paginate(10);
        }else{
            $transactions = $vcard->transactions()->orderBy('datetime', 'desc')->paginate(10);
        }

        return response()->json([
            $transactions,
            'last' => $transactions->lastPage(),
        ], 200); // HTTP 200 OK


    }*/

    public function getMyTransactions() {
        $vcard = Auth::user();
        $transactions = $vcard->transactions()->orderBy('datetime', 'desc')->paginate(10);
        // $transformedTransactions = TransactionResource::collection($transactions);

        // return response()->json([
        //     $transactions,
        //     'last' => $transactions->lastPage(),
        // ], 200); // HTTP 200 OK

        return $this->responseService->sendWithDataResponse(200, null, ["transactions" => $transactions, "last" => $transactions->lastPage()]);

    }

    public function indexType(Request $request){

        $validator = Validator::make($request->all(), [
            'type' => 'required|in:all,D,C',
        ]);

        if($validator->fails()){
            return $this->errorService->sendValidatorError(422, "Validation Failed", $validator->errors());
        }


        if($request->type != 'all'){
            $transactions = Transaction::where('type', $request->type)->orderBy('datetime', 'desc')->paginate(10);
        }else{
            $transactions = Transaction::orderBy('datetime', 'desc')->paginate(10);
        }

        // return response()->json([
        //     $transactions,
        //     'last' => $transactions->lastPage(),
        // ], 200); // HTTP 200 OK

        return $this->responseService->sendWithDataResponse(200, null, ['transactions' => $transactions, 'last' => $transactions->lastPage()]);

    }


}
