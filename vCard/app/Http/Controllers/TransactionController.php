<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Vcard;
use App\Services\ErrorService;
use App\Services\ResponseService;
use App\Services\TransactionService;

class TransactionController extends Controller
{

    protected $errorService;
    protected $responseService;
    protected $transactionService;

    public function __construct(){
        $this->errorService = new ErrorService();
        $this->responseService = new ResponseService();
        $this->transactionService = new TransactionService();
    }

    public function index(){
        $transactions = Transaction::orderBy('date', 'desc')->paginate(10);
        return $this->responseService->sendWithDataResponse(200, "All Transactions retrieved successfully", ['transactions' => $transactions, 'last' => $transactions->lastPage()]);
    }

    public function show(int $id){
        $transaction = Transaction::find($id);

        if($transaction){
            $vcard = Auth::user();
            if(!$vcard->transactions()->where('id', $id)->exists()){
                return $this->errorService->sendStandardError(403, "You are not authorized to view this transaction");
            }

            return $this->responseService->sendWithDataResponse(200, "Transaction retrieved successfully", ['transaction' => $transaction]);
        }

        return $this->errorService->sendStandardError(404, "Transaction not found");
    }

    public function update(Request $request, int $id){
        $transaction = Transaction::find($id);

        if($transaction){
            $vcard = Auth::user();
            if(!$vcard->transactions()->where('id', $id)->exists()){
                return $this->errorService->sendStandardError(403, "You are not authorized to update this transaction");
            }

            $validator = Validator::make($request->all(), [
                'category' => 'exists:categories,id',
            ]);

            //se a categoria n for do vcard do auth
            if($request->category != $vcard->categories()->where('id', $request->category)->exists()){
                return $this->errorService->sendStandardError(403, "This category does not belong to you");
            }

            if($validator->fails()){
                return $this->errorService->sendValidatorError(422, "Validation Failed", $validator->errors());
            }

            $transaction->description = $request->description;
            $transaction->category_id = $request->category;

            if($transaction->save()){
                return $this->responseService->sendStandardResponse(200, "Transaction updated successfully");
            }
            return $this->errorService->sendStandardError(500, "Transaction could not be updated");
        }

        return $this->errorService->sendStandardError(404, "Transaction not found");

    }

    public function indexAllTransactions_search(string $query, Request $request){

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

        if($request->type != 'all'){
            $transactions = $transactions->where('type', $request->type)->paginate(10);
        }else{
            $transactions = $transactions->paginate(10);
        }

        if($transactions){
            return $this->responseService->sendWithDataResponse(200, "Transactions retrieved successfully", ['transactions' => $transactions, 'last' => $transactions->lastPage()]);
        }
        return $this->errorService->sendStandardError(404, "The vcard with that phone number does not have any transactions");

    }

    public function indexMyTransactions_search(string $query, Request $request){
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:all,D,C',
        ]);

        if($validator->fails()){
            return $this->errorService->sendValidatorError(422, "Validation Failed", $validator->errors());
        }

        $vcard = Auth::user();

        switch ($query){
            case Str::startsWith($query, '9') && strlen($query) == 9 && is_numeric($query): //phone number
                $transactions = $vcard->transactions()->where('pair_vcard', $query)->orderBy('datetime', 'desc');
                break;
            case Str::contains($query, '@'): //email
                $phone = Vcard::where('email', $query)->select('phone_number');
                $transactions = $vcard->transactions()->where('pair_vcard', $phone)->orderBy('datetime', 'desc');
                break;
            default: //name
                $phone = Vcard::where('name', 'LIKE', '%' . $query . '%')->pluck('phone_number');
                $transactions = $vcard->transactions()->whereIn('pair_vcard', $phone)->orderBy('datetime', 'desc');
                break;
        }

        if($request->type != 'all'){
            $transactions = $transactions->where('type', $request->type)->paginate(10);
        }else{
            $transactions = $transactions->paginate(10);
        }

        if($transactions){
            return $this->responseService->sendWithDataResponse(200, "Transactions retrieved successfully", ['transactions' => $transactions, 'last' => $transactions->lastPage()]);
        }
        return $this->errorService->sendStandardError(404, "The vcard with that phone number does not have any transactions");
    }

    public function MyTransactionsType(Request $request){

        $validator = Validator::make($request->all(), [
            'type' => 'required|in:all,D,C',
        ]);

        if($validator->fails()){
            return $this->errorService->sendStandardError(422, $validator->errors());
        }

        $vcard = Auth::user();

        if($request->type != 'all'){
            $transactions = $vcard->transactions()->where('type', $request->type)->orderBy('datetime', 'desc')->paginate(10);
        }else{
            $transactions = $vcard->transactions()->orderBy('datetime', 'desc')->paginate(10);
        }

        return $this->responseService->sendWithDataResponse(200, null, ["transactions" => $transactions, "last" => $transactions->lastPage()]);
    }

    public function getMyTransactions() {
        $vcard = Auth::user();
        $transactions = $vcard->transactions()->orderBy('datetime', 'desc')->paginate(10);


        return $this->responseService->sendWithDataResponse(200, null, ["transactions" => $transactions, "last" => $transactions->lastPage()]);

    }

    public function indexAllTransactions_type(Request $request){

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

        return $this->responseService->sendWithDataResponse(200, null, ['transactions' => $transactions, 'last' => $transactions->lastPage()]);
    }

    public function creditVcard(Request $request){
        $validator = Validator::make($request->all(), [
            'vcard' => 'min:9',
            'amount' => 'required|numeric',
            'payment_type' => ['required', 'in:MBWAY,PAYPAL,IBAN,MB,VISA'],
        ]);

        if ($validator->fails()) {
            return $this->errorService->sendValidatorError(422, "Validation Failed", $validator->errors());
        }

        if ($request->amount <= 0.00) {
            return $this->errorService->sendStandardError(422, "Amount needs to be greater than 0.00");
        }

        $vcard = Vcard::find($request->vcard);
        // $vcard_destination = Vcard::where('phone_number', $request->phone_number)->first();
        if(!$vcard) {
            return $this->errorService->sendStandardError(404, "Vcard not found");
        }

        $transactionReturn = null;
        switch ($request->payment_type) {
            case "MB":
                $transactionReturn = $this->transactionService->mb($vcard, $request, 'C');
                break;

            case "IBAN":
                $transactionReturn = $this->transactionService->iban($vcard, $request, 'C');
                break;

            case "VISA":
                $transactionReturn = $this->transactionService->visa($vcard, $request, 'C');
                break;

            case "PAYPAL":
                $transactionReturn = $this->transactionService->paypal($vcard, $request, 'C');
                break;

            case "MBWAY":
                $transactionReturn = $this->transactionService->mbway($vcard, $request, 'C');
                break;

            default:
                $transactionReturn = $this->errorService->sendStandardError(500, "The current payment method does not exist or is not supported");
                break;
        }
        if($transactionReturn != null){
            return $transactionReturn;
        }

        return $this->responseService->sendStandardResponse(200, "Transaction Successfully");

    }
}
