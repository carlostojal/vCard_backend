<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PDF;
use Illuminate\Support\Facades\Storage;
use App\Models\Transaction;
use App\Services\ErrorService;
use App\Models\DefaultCategory;

class PDFController extends Controller
{
    
    protected $errorService;

    public function __construct(){
        $this->errorService = new ErrorService();
        //$this->responseService = new ResponseService();
    }

    public function index(Request $request){

        $validator = Validator::make($request->all(), [
            'month' => 'required|integer',
            'year' => 'required|integer',
        ]);

        if($validator->fails()){
            return $this->errorService->sendError(422, "Validation Failed", $validator->errors());
        }

        $transactions = Transaction::whereMonth('date', '=', $request->month)->whereYear('date', '=', $request->year)->get();

        $data = [
            'transactions' => $transactions,
            'month' => $request->month,
            'year' => $request->year,
        ];

        $pdf = PDF::loadView('my_pdf', $data);

        $pdfPath = 'pdf/extract/' . uniqid() . '.pdf';
        Storage::disk('local')->put($pdfPath, $pdf->output());

        //get the pdf
        $pdfContents = Storage::disk('local')->get($pdfPath);

        return response($pdfContents, 200, [
            'Content-Type' => 'application/pdf',
        ]);

    }

}
