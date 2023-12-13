<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Vcard;
use App\Rules\IbanReference;
use App\Rules\MbReference;
use App\Rules\PaypalReference;
use App\Rules\VisaReference;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TransactionService
{
    protected $errorService;
    protected $responseService;
    protected $guzzleCLient;
    protected $paymentApiUrl;

    public function __construct(){
        $this->errorService = new ErrorService();
        $this->responseService = new ResponseService();
        $this->guzzleCLient = new Client();
        $this->paymentApiUrl = env('PAYMENT_API_URL');
    }

    private function postPaymentApiDebit(String $referenceType, String $reference, Float $value): bool {
        $res = $this->guzzleCLient->request('POST', $this->paymentApiUrl . '/debit', [
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
    }

    private function postPaymentApiCredit(String $referenceType, String $reference, Float $value): bool {
        try{
            $res = $this->guzzleCLient->request('POST', $this->paymentApiUrl . '/credit', [
                'json' => [
                    'type' => $referenceType,
                    'reference' => $reference,
                    'value' => $value,
                ]
            ]);
            if($res->getStatusCode() >= 200){
                return true;
            }
        }catch(Exception $e){
            return false;
        }
    }

    private function createTransaction(Vcard $vcard, String $type, Float $value, Float $newBalance,
        String $paymentType, ?String $pairVcard, String $reference, ?String $description = null)
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

        if ($description) {
            $transaction->description = $description;
        }
        return $transaction;
    }

    private function makeDebitTransaction(Vcard $vcard, Float $amount, String $paymentType, String $reference, ?String $description): bool{
        try {
            DB::beginTransaction();

            $newBalance = ($vcard->balance - $amount);
            $t = $this->createTransaction($vcard, 'D', $amount, $newBalance, $paymentType, null, $reference, $description);
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
            $destination_new_balance = ($vcard_destination->balance + $req->amount);

            $t1 = $this->createTransaction($vcard_origin, 'D', $req->amount, $origin_new_balance, 'VCARD', $vcard_destination->phone_number, $vcard_destination->phone_number, $req->description);
            $t2 = $this->createTransaction($vcard_destination, 'C', $req->amount, $destination_new_balance, 'VCARD', $vcard_origin->phone_number, $vcard_origin->phone_number, $req->description);

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

    public function mb(Vcard $vcard_origin, Request $req){
        $validator = Validator::make($req->all(), [
            'payment_reference' => ['required', new MbReference],
        ]);

        if ($validator->fails()) {
            return $this->errorService->sendValidatorError(422, "Validation Failed", $validator->errors());
        }

        $error = $this->processTransaction($vcard_origin, $req, "MB");
        if ($error){
            return $error;
        }
        return null;
    }

    public function iban(Vcard $vcard_origin, Request $req){
        $validator = Validator::make($req->all(), [
            'payment_reference' => ['required', new IbanReference],
        ]);

        if ($validator->fails()) {
            return $this->errorService->sendValidatorError(422, "Validation Failed", $validator->errors());
        }

        $error = $this->processTransaction($vcard_origin, $req, "IBAN");
        if($error){
            return $error;
        }
        return null;
    }

    public function visa(Vcard $vcard_origin, Request $req){
        $validator = Validator::make($req->all(), [
            'payment_reference' => ['required', new VisaReference],
        ]);

        if ($validator->fails()) {
            return $this->errorService->sendValidatorError(422, "Validation Failed", $validator->errors());
        }

        $error = $this->processTransaction($vcard_origin, $req, "VISA");
        if($error){
            return $error;
        }
        return null;
    }

    public function paypal(Vcard $vcard_origin, Request $req){
        $validator = Validator::make($req->all(), [
            'payment_reference' => ['required', new PaypalReference],
        ]);

        if ($validator->fails()) {
            return $this->errorService->sendValidatorError(422, "Validation Failed", $validator->errors());
        }

        $error = $this->processTransaction($vcard_origin, $req, "PAYPAL");
        if($error){
            return $error;
        }
        return null;
    }

    public function mbway(Vcard $vcard_origin, Request $req){
        $validator = Validator::make($req->all(), [
            'payment_reference' => ['required', new PaypalReference],
        ]);

        if ($validator->fails()) {
            return $this->errorService->sendValidatorError(422, "Validation Failed", $validator->errors());
        }

        $error = $this->processTransaction($vcard_origin, $req, "mbway");
        if($error){
            return $error;
        }
        return null;
    }

    private function processTransaction(Vcard $vcard, Request $req, String $paymentType){
        if ($this->postPaymentApiCredit($paymentType, $req->payment_reference, $req->amount) == false) {
            return $this->errorService->sendStandardError(500, "Transaction couldn't be performed, entity error");
        }

        if ($this->makeDebitTransaction($vcard, $req->amount, $paymentType, $req->payment_reference, $req->description) == false){
            return $this->errorService->sendStandardError(500, "Transaction couldn't be performed, entity error");
        }

        return null;
    }
}
