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

    private function applyTransactionFilters($transactions, Request $request){
        if($request->has('type')){
            $transactions->where('type', $request->type);
        }
        if($request->has('vcard')){
            $transactions->where('vcard', $request->vcard);
        }
        if($request->has('pair_vcard')){
            $phone = null;
            if (filter_var($request->pair_vcard, FILTER_VALIDATE_EMAIL)) {
                $phone = Vcard::where('email', $request->pair_vcard)->select('phone_number');
            }elseif(is_numeric($request->pair_vcard)){
                $phone = Vcard::where('phone_number', $request->pair_vcard)->select('phone_number');
            }elseif(is_string($request->pair_vcard)){
                $phone = Vcard::where('name', $request->pair_vcard)->select('phone_number');
            }
            if($phone != null){
                $transactions->where('pair_vcard', $phone);
            }
        }
        return $transactions;
    }

    public function index(?Vcard $vcard = null, Request $request){
        $user = Auth::user();
        if($user instanceof Vcard && $vcard == null){
            $vcard = $user;
        }
        if($vcard){
            $transactions = $vcard->transactions()->orderBy('datetime', 'desc');
        }
        $transactions = Transaction::orderBy('datetime', 'desc');

        if($request->all() != null){
            $transactions = $this->applyTransactionFilters($transactions, $request);
        }
        $transactions = $transactions->paginate(10);
        return $this->responseService->sendWithDataResponse(200, "All Transactions retrieved successfully", ['transactions' => $transactions, 'last' => $transactions->lastPage()]);
    }

    public function show(Request $request, Transaction $transaction){
        return $this->responseService->sendWithDataResponse(200, "Transaction retrieved successfully", ['transaction' => $transaction]);
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
