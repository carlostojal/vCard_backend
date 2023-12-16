<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Vcard;
use Illuminate\Support\Facades\Auth;
use App\Services\ResponseService;
use App\Http\Resources\TransactionResource;
use App\Models\Categories;
class StatisticsController extends Controller
{
    protected $responseService;
    public function __construct(ResponseService $responseService)
    {
        $this->responseService = $responseService;
    }

    public function getStatisticsDebitPerMonth()
    {
        $vcard = Auth::user();
        $transactions = $vcard->transactions()->orderBy('datetime', 'desc')->where("type", "D")->whereYear("date", 2023)->select('value', 'date')->get();
        $sum = 0.0;
        $data = [];
        foreach ($transactions as $transaction) {
            $month = substr($transaction->date, 5, 2);
            if (isset($data[$month - 1])) {
                $data[$month - 1] = $transaction->value + $data[$month - 1];
            } else {
                $data[$month - 1] = $transaction->value + 0;
            }
        }
        return $this->responseService->sendWithDataResponse(200, null, [$data]);
    }
    public function getStatisticsDebitPerYear()
    {
        $vcard = Auth::user();
        $transactions = $vcard->transactions()->orderBy('datetime', 'desc')->where("type", "D")->whereYear("date", 2023)->select('value', 'date')->get();
        $sum = 0.0;
        $data = [];
        foreach ($transactions as $transaction) {
            $year = $transaction->date[3];
            if (isset($data[$year])) {
                $data[$year] = $transaction->value + $data[$year];
            } else {
                $data[$year] = $transaction->value + 0;
            }
        }
        return $this->responseService->sendWithDataResponse(200, null, [$data]);
    }
    public function getStatisticsCreditPerMonth()
    {
        $vcard = Auth::user();
        $transactions = $vcard->transactions()->orderBy('datetime', 'desc')->where("type", "C")->whereYear("date", 2023)->select('value', 'date')->get();
        $data = [];
        foreach ($transactions as $transaction) {
            $month = substr($transaction->date, 5, 2);
            if (isset($data[$month - 1])) {
                $data[$month - 1] = $transaction->value + $data[$month - 1];
            } else {
                $data[$month - 1] = $transaction->value + 0;
            }
        }
        return $this->responseService->sendWithDataResponse(200, null, [$data]);
    }
    public function getStatisticsCreditPerYear()
    {
        $vcard = Auth::user();
        $transactions = $vcard->transactions()->orderBy('datetime', 'desc')->where("type", "C")->whereYear("date", 2023)->select('value', 'date')->get();
        $data = [];
        foreach ($transactions as $transaction) {
            $year = $transaction->date[3];
            if (isset($data[$year])) {
                $data[$year] = $transaction->value + $data[$year];
            } else {
                $data[$year] = $transaction->value + 0;
            }
        }
        return $this->responseService->sendWithDataResponse(200, null, [$data]);
    }
    public function getMoneySpentPerCardType()
    {
        $vcard = Auth::user();
        $transactions = $vcard->transactions()->orderBy('datetime', 'desc')->where("type", "D")->whereYear("date", 2023)->select('value', 'payment_type')->get();
        $sum = 0.0;
        $data = [];
        foreach ($transactions as $transaction) {
            if (isset($data[$transaction->payment_type])) {
                $data[$transaction->payment_type] = $transaction->value + $data[$transaction->payment_type];
            } else {
                $data[$transaction->payment_type] = $transaction->value;
            }
        }
        return $this->responseService->sendWithDataResponse(200, null, [$data]);
    }
    public function getMoneyReceivedPerCardType()
    {
        $vcard = Auth::user();
        $transactions = $vcard->transactions()->orderBy('datetime', 'desc')->where("type", "C")->whereYear("date", 2023)->select('value', 'payment_type')->get();
        $sum = 0.0;
        $data = [];
        foreach ($transactions as $transaction) {
            if (isset($data[$transaction->payment_type])) {
                $data[$transaction->payment_type] = $transaction->value + $data[$transaction->payment_type];
            } else {
                $data[$transaction->payment_type]= $transaction->value;
            }
        }
        return $this->responseService->sendWithDataResponse(200, null, [$data]);
    }
    public function getMoneySpentByCategories(){
        $vcard = Auth::user();
        $transactions = $vcard->transactions()->join('categories','transactions.category_id','=','categories.id')
        ->where('transactions.type', 'D')
        ->select('transactions.value','categories.id','categories.name')
        ->get();
        $data=[];
        $max=0;
        $sum=0;
        foreach ($transactions as $transaction) {
            if (isset($data[$transaction->name])) {
                
                $data[$transaction->name] = $transaction->value + $data[$transaction->name];
                if($max<$data[$transaction->name]){
                    $max=$data[$transaction->name];
                    $maxCategory = $transaction->name;
                }
            } else {
                if($max<$transaction->value){
                $max=$transaction->value;
                $maxCategory = $transaction->name;
                }
                $data[$transaction->name] = $transaction->value + 0;
            }
            $sum+=$transaction->value;
        }
        return $this->responseService->sendWithDataResponse(200, null,  ["max" =>$max,"perc"=>number_format($max/$sum,4)*100,"maxCategory"=>$maxCategory,$data]);
    }
    public function getMoneyReceivedByCategories(){
        $vcard = Auth::user();
        $transactions = $vcard->transactions()->join('categories','transactions.category_id','=','categories.id')
        ->where('transactions.type', 'C')
        ->select('transactions.value','categories.id','categories.name')
        ->get();
        $data=[];
        $max=0;
        $sum=0;
        foreach ($transactions as $transaction) {
            if (isset($data[$transaction->name])) {
                
                $data[$transaction->name] = $transaction->value + $data[$transaction->name];
                if($max<$data[$transaction->name]){
                    $max=$data[$transaction->name];
                    $maxCategory = $transaction->name;
                }
            } else {
                if($max<$transaction->value){
                $max=$transaction->value;
                $maxCategory = $transaction->name;
                }
                $data[$transaction->name] = $transaction->value + 0;
            }
            $sum+=$transaction->value;
        }
        return $this->responseService->sendWithDataResponse(200, null,  ["max" =>$max,"perc"=>number_format($max/$sum,4)*100,"maxCategory"=>$maxCategory,$data]);
    }
}
