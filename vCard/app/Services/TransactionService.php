<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Vcard;
use GuzzleHttp\Client;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    private function postPaymentAPI(String $referenceType, String $route, String $reference, Float $value): bool{
        $res = $this->guzzleCLient->request('POST', $this->paymentApiUrl . '/' . $route, [
            'form_params' => [
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

    private function createTransaction(Vcard $vcard, $type, $value, $newBalance, $paymentType, $pairVcard, $reference, $description = null)
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

    public function vcard(Vcard $vcard_origin, Vcard $vcard_destination, Request $req): bool{
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

            return true;
        }catch(QueryException $e) {
            DB::rollBack();
            return false;
        }
    }

    public function mb(Vcard $vcard_origin, Vcard $vcard_destination, Request $req): bool{
        try {
            DB::beginTransaction();

            $origin_new_balance = ($vcard_origin->balance - $req->amount);
            $destination_new_balance = ($vcard_destination->balance + $req->amount);

            $t1 = $this->createTransaction($vcard_origin, 'D', $req->amount, $origin_new_balance, 'MB',null, $vcard_destination->phone_number, $req->description);
            $t2 = $this->createTransaction($vcard_destination, 'C', $req->amount, $destination_new_balance, 'MB', null, $vcard_origin->phone_number, $req->description);

            $t1->pair_transaction = $t2->id;
            $t2->pair_transaction = $t1->id;

            $vcard_origin->balance = $origin_new_balance;
            $vcard_destination->balance = $destination_new_balance;

            $vcard_origin->save();
            $vcard_destination->save();

            $t1->save();
            $t2->save();

            DB::commit();

            return true;
        }catch(QueryException $e) {
            DB::rollBack();
            return false;
        }
    }

    private function generate
}
