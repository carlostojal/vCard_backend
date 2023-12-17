<?php
namespace App\Http\Controllers;

use App\Models\DefaultCategory;
use App\Services\ErrorService;
use App\Services\ResponseService;

class DefaultCategoryController extends Controller
{
    protected $errorService;
    protected $responseService;

    public function __construct(){
        $this->errorService = new ErrorService();
        $this->responseService = new ResponseService();
    }

    public function index(){
        $categories = DefaultCategory::paginate(15);
        return $this->responseService->sendWithDataResponse(200, null, ['categories' => $categories, 'lastPage' => $categories->lastPage()]);
    }

    public function show(DefaultCategory $defaultCategory){
        return $this->responseService->sendWithDataResponse(200, null, $defaultCategory);
    }

}


