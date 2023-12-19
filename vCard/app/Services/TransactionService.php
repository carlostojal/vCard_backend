<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Vcard;
use App\Rules\IbanReference;
use App\Rules\MbReference;
use App\Rules\MbwayReference;
use App\Rules\PaypalReference;
use App\Rules\VisaReference;
use GuzzleHttp\Client;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Services\ErrorService;
use App\Services\ResponseService;
use Exception;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use App\Models\PiggyBank;

class TransactionService
{
    protected $errorService;
    protected $responseService;
    protected $guzzleClient;
    protected $paymentApiUrl;
    protected $errorMessage;

    public function __construct(){
        $this->errorService = new ErrorService();
        $this->responseService = new ResponseService();
        $this->guzzleClient = new Client();
        $this->paymentApiUrl = env('PAYMENT_API_URL');
    }

    private function setErrorMessage($e){
        $this->errorMessage = json_decode($e->getResponse()->getBody()->getContents(), true)['message'];
    }

    private function postPaymentApiDebit(String $referenceType, String $reference, Float $value): bool {
        try {
            $res = $this->guzzleClient->request('POST', $this->paymentApiUrl . '/debit', [
                'json' => [
                    'type' => $referenceType,
                    'reference' => $reference,
                    'value' => $value,
                ]
            ]);
            if($res->getStatusCode() >= 200){
                return true;
            }else {
                $this->errorMessage = $res->message;
                return false;
            }
        }catch(ClientException $e){
            $this->setErrorMessage($e);
            return false;
        }
    }

    private function postPaymentApiCredit(String $referenceType, String $reference, Float $value): bool {
        try{
            $res = $this->guzzleClient->request('POST', $this->paymentApiUrl . '/credit', [
                'json' => [
                    'type' => $referenceType,
                    'reference' => $reference,
                    'value' => $value,
                ]
            ]);
            if($res->getStatusCode() >= 200){
                return true;
            }else {
                return false;
            }
        }catch(Exception $e){
            return false;
        }
    }

    private function createTransaction(Vcard $vcard, String $type, Float $value, Float $newBalance,
        String $paymentType, ?String $pairVcard, String $reference, ?String $description = null, ?String $category_id)
    {
        $dt = now();

        $transaction = Transaction::create([
            'vcard' => $vcard->phone_number,
            'date' => $dt->format('Y-m-d'),
            'datetime' => $dt->format('Y-m-d H:i:s'),
            'type' => $type,
            'value' => $value,
            'old_balance' => $vcard->balance,
            'new_balance' => $newBalance,
            'payment_type' => $paymentType,
            'pair_vcard' => $pairVcard,
            'payment_reference' => $reference,
        ]);

        if($description) {
            $transaction->description = $description;
        }
        if($category_id){
            $transaction->category_id = $category_id;
        }
        return $transaction;
    }

    private function makeDebitTransaction(Vcard $vcard, Float $amount, String $paymentType, String $reference, ?String $description, ?String $category_id): bool{
        try {
            DB::beginTransaction();

            $newBalance = ($vcard->balance - $amount);
            $t = $this->createTransaction($vcard, 'D', $amount, $newBalance, $paymentType, null, $reference, $description, $category_id);
            $vcard->balance = $newBalance;
            $vcard->save();
            $t->save();

            DB::commit();
            return true;
        }catch(QueryException $e) {
            DB::rollBack();
            return false;
        }
    }

    private function makeCreditTransaction(Vcard $vcard, Float $amount, String $paymentType, String $reference, ?String $description, ?String $category_id): bool{
        try {
            DB::beginTransaction();

            $newBalance = ($vcard->balance + $amount);
            $t = $this->createTransaction($vcard, 'C', $amount, $newBalance, $paymentType, null, $reference, $description, $category_id);
            $vcard->balance = $newBalance;
            $vcard->save();
            $t->save();

            DB::commit();
            return true;
        }catch(QueryException $e) {
            DB::rollBack();
            return false;
        }
    }


    public function vcard(Vcard $vcard_origin, Vcard $vcard_destination, Request $req){
        try {
            DB::beginTransaction();

            $origin_new_balance = ($vcard_origin->balance - $req->amount);

            //Se tiver flag para o arredondamento do amount para o piggybank - TAES
            if($req->flagRestPiggyBank){

                //Arredonda o amount para o piggybank
                $restMoney = round(ceil($req->amount) - $req->amount, 2);

                //Se o vcard não ficar com 0 de saldo
                if($vcard_origin->balance != $req->amount){
                    if($restMoney > 0){
                        $origin_new_balance = $origin_new_balance - $restMoney; //Tira o dinheiro que vai para o piggybank

                        $piggibank = PiggyBank::where('vcard_phone_number', $vcard_origin->phone_number)->first();
                        $piggibank->balance = $piggibank->balance + $restMoney;
                        $piggibank->save();
                    }
                }
            }


            $destination_new_balance = ($vcard_destination->balance + $req->amount);

            $t1 = $this->createTransaction($vcard_origin, 'D', $req->amount, $origin_new_balance, 'VCARD', $vcard_destination->phone_number, $vcard_destination->phone_number, $req->description, $req->category_id);
            $t2 = $this->createTransaction($vcard_destination, 'C', $req->amount, $destination_new_balance, 'VCARD', $vcard_origin->phone_number, $vcard_origin->phone_number, $req->description, $req->category_id);

            $t1->pair_transaction = $t2->id;
            $t2->pair_transaction = $t1->id;

            $vcard_origin->balance = $origin_new_balance;
            $vcard_destination->balance = $destination_new_balance;

            $vcard_origin->save();
            $vcard_destination->save();

            $t1->save();
            $t2->save();

            DB::commit();
            return;
        }catch(QueryException $e) {
            DB::rollBack();
            return $this->errorService->sendStandardError(500, "Transaction couldn't be performed");
        }
    }

    public function mb(Vcard $vcard, Request $req, $transactionType){
        return $this->make($vcard, $req, $transactionType, new MbReference);
    }

    public function iban(Vcard $vcard_origin, Request $req, $transactionType){
        return $this->make($vcard_origin, $req, $transactionType, new IbanReference);
    }

    public function visa(Vcard $vcard_origin, Request $req, $transactionType){
        return $this->make($vcard_origin, $req, $transactionType, new VisaReference);
    }

    public function paypal(Vcard $vcard_origin, Request $req, $transactionType){
        return $this->make($vcard_origin, $req, $transactionType, new PaypalReference);
    }

    public function mbway(Vcard $vcard, Request $req, $transactionType){
        return $this->make($vcard, $req, $transactionType, new MbwayReference);
    }

    private function make(Vcard $vcard_origin, Request $req, $transactionType, $referenceRuleClass){
        $validator = Validator::make($req->all(), [
            'payment_reference' => ['required', $referenceRuleClass],
        ]);

        if ($validator->fails()) {
            return $this->errorService->sendValidatorError(422, "Validation Failed", $validator->errors());
        }
        if($transactionType == 'D'){
            $error = $this->processDebitTransaction($vcard_origin, $req, $req->payment_type);
        }elseif($transactionType == 'C'){
            $error = $this->processCreditTransaction($vcard_origin, $req, $req->payment_type);
        }else {
            $error = $this->errorService->sendStandardError(500, 'Transacation type needs to be C or D');
        }
        if($error){
            return $error;
        }
        return null;
    }

    private function processDebitTransaction(Vcard $vcard, Request $req, String $paymentType){
        $this->errorMessage = null;
        if ($this->postPaymentApiCredit($paymentType, $req->payment_reference, $req->amount) == false) {
            return $this->errorService->sendStandardError(500, "Transaction couldn't be performed, entity error. ".$this->errorMessage);
        }

        if ($this->makeDebitTransaction($vcard, $req->amount, $paymentType, $req->payment_reference, $req->description, $req->categoy_id) == false){
            return $this->errorService->sendStandardError(500, "Transaction couldn't be performed, entity error. ".$this->errorMessage);
        }

        return null;
    }

    private function processCreditTransaction(Vcard $vcard, Request $req, String $paymentType){
        $this->errorMessage = null;
        if ($this->postPaymentApiDebit($paymentType, $req->payment_reference, $req->amount) == false) {
            return $this->errorService->sendStandardError(500, "Transaction couldn't be performed, entity error. ".$this->errorMessage);
        }

        if ($this->makeCreditTransaction($vcard, $req->amount, $paymentType, $req->payment_reference, $req->description, $req->category_id) == false){
            return $this->errorService->sendStandardError(500, "Transaction couldn't be performed, entity error. ".$this->errorMessage);
        }

        return null;
    }
}
