<?php
namespace App\Http\Controllers;

use App\Models\DefaultCategory;
use App\Services\ErrorService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DefaultCategoryController extends Controller
{
    protected $errorService;
    protected $responseService;

    public function __construct(){
        $this->errorService = new ErrorService();
        $this->responseService = new ResponseService();
    }

    private function applyDefaultCategoryFilters($defaultCategories, Request $request){
        if($request->has('name')){
            $defaultCategories->where('name', 'LIKE', '%'.$request->name.'%');
        }
        if($request->has('type') && $request->type != 'all'){
            $defaultCategories->where('type', $request->type);
        }

        return $defaultCategories;
    }

    public function index(Request $request){
        $categories = DefaultCategory::query();
        if($request->all() != null){
            $categories = $this->applyDefaultCategoryFilters($categories, $request);
        }else {
            $categories->orderBy('id', 'asc');
        }
        $categories = $categories->paginate(15);
        return $this->responseService->sendWithDataResponse(200, null, ['categories' => $categories, 'lastPage' => $categories->lastPage()]);
    }

    public function show(DefaultCategory $defaultCategory){
        return $this->responseService->sendWithDataResponse(200, null, $defaultCategory);
    }

    public function destroy(DefaultCategory $defaultCategory){
        $defaultCategory->delete();
        return $this->responseService->sendStandardResponse(204, 'Default Category Deleted');
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:default_categories',
            'type' => 'required|in:D,C',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid category',
                'errors' => $validator->errors(),
            ], 422);
        }

        $category = DefaultCategory::create([
            'name' => $request->name,
            'type' => $request->type,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $category,
        ], 201);
    }

}


