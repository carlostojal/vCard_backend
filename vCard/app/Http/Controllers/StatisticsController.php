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
    public function getAllTransactionsDebit()
    {
        
        $transactions = Transaction::selectRaw('SUM(value) as total_value, MONTH(datetime) as month')
        ->where('type', 'D')
        ->whereYear('datetime', 2023)
        ->groupByRaw('MONTH(datetime)')
        ->orderBy('month', 'desc')
        ->get();
        return $this->responseService->sendWithDataResponse(200, null, $transactions);
    }
    public function getAllTransactionsCredit()
    {
        
        $transactions = Transaction::selectRaw('SUM(value) as total_value, MONTH(datetime) as month')
        ->where('type', 'C')
        ->whereYear('datetime', 2023)
        ->groupByRaw('MONTH(datetime)')
        ->orderBy('month', 'desc')
        ->get();
        return $this->responseService->sendWithDataResponse(200, null, $transactions);
    }
    public function getAllTransactionsDebitYear()
    {
        
        $transactions = Transaction::selectRaw('SUM(value) as total_value, YEAR(datetime) as year')
        ->where('type', 'D')
        ->groupByRaw('YEAR(datetime)')
        ->orderBy('year', 'desc')
        ->get();
        return $this->responseService->sendWithDataResponse(200, null, $transactions);
    }
    public function getAllTransactionsCreditYear()
    {
        
        $transactions = Transaction::selectRaw('SUM(value) as total_value, YEAR(datetime) as year')
        ->where('type', 'C')
        ->groupByRaw('YEAR(datetime)')
        ->orderBy('year', 'desc')
        ->get();
        return $this->responseService->sendWithDataResponse(200, null, $transactions);
    }
    public function getAllTransactionbByTypeDebit(){
                
        $transactions = Transaction::select('value', 'date')->orderBy('datetime', 'desc')->where("type", "D")->whereYear("date", 2023)->get();
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
    public function getAllTransactionbByTypeCredit(){
                
        $transactions = Transaction::select('value', 'payment_type')->orderBy('datetime', 'desc')->where("type", "C")->whereYear("date", 2023)->get();
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
    public function getActiveVcards(){
        $vcardsThisMonth = Vcard::where('blocked', 0)
        ->whereYear('created_at', now()->year)
        ->whereMonth('created_at', now()->month)
        ->count();
        $vcardsActive = Vcard::where('blocked', 0)
        ->count();
        $sumOfVcardsbalance = Vcard::where('blocked', 0)->sum('balance');

        $sumOfTotalTransactions = Transaction::orderBy('datetime','desc')->count();

        $transactionsByType = Transaction::selectRaw('SUM(value) as total_value,payment_type as type')
        ->groupByRaw('payment_type')
        ->get();
        return $this->responseService->sendWithDataResponse(200, null, ["currentMonth"=>$vcardsThisMonth,"active"=>$vcardsActive,"sumOfBalance" =>$sumOfVcardsbalance,"sumofTransactions"=>$sumOfTotalTransactions,"transactionsByType"=>$transactionsByType]);

    }
}