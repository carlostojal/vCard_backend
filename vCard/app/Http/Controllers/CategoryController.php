<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use App\Models\DefaultCategory;
use App\Models\Category;
use App\Models\Vcard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\ErrorService;
use App\Services\ResponseService;

class CategoryController extends Controller
{

    protected $errorService;
    protected $responseService;

    public function __construct(){
        $this->errorService = new ErrorService();
        $this->responseService = new ResponseService();
    }

    public function index(?Vcard $vcard = null){
        if($vcard){
            $categories = $vcard->categories()->paginate(15);
            return $this->responseService->sendWithDataResponse(200, null, ['categories' => $categories, 'lastPage' => $categories->lastPage()]);
        }
        $categories = Category::paginate(15);
        return $this->responseService->sendWithDataResponse(200, null, ['categories' => $categories, 'lastPage' => $categories->lastPage()]);
    }

    public function show(Category $category){
        return $this->responseService->sendWithDataResponse(200, null, $category);
    }

    public function show_2(String $query, Request $request){
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:D,C,all',
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid type',
                'errors' => $validator->errors(),
            ], 422);
        }

        if($request->type == 'all'){
            $categories = DefaultCategory::where('name', 'like', '%'.$query.'%')->paginate(15);
            return response()->json([
                'status' => 'success',
                'data' => $categories,
                'lastPage' => $categories->lastPage(),
            ]);
        }else{
            $type = $request->query('type');
            $categories = DefaultCategory::where('name', 'like', '%'.$query.'%')->where('type', $type)->paginate(15);
            return response()->json([
                'status' => 'success',
                'data' => $categories,
                'lastPage' => $categories->lastPage(),
            ]);
        }

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

    public function storeMyCategoriesDAD(Request $request){
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

        $category = Category::create([
            'vcard' => Auth::user()->phone_number,
            'name' => $request->name,
            'type' => $request->type,
        ]);

        return $this->responseService->sendWithDataResponse(200, null, ['category' => $category]);
    }

    public function indexType(Request $request){

        $validator = Validator::make($request->all(), [
            'type' => 'required|in:D,C,all',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid type',
                'errors' => $validator->errors(),
            ], 422);
        }

        if($request->type == 'all'){
            $categories = DefaultCategory::paginate(15);
            return response()->json([
                'status' => 'success',
                'data' => $categories,
                'lastPage' => $categories->lastPage(),
            ]);
        }else{
            $type = $request->query('type');
            $categories = DefaultCategory::where('type', $type)->paginate(15);
            return response()->json([
                'status' => 'success',
                'data' => $categories,
                'lastPage' => $categories->lastPage(),
            ]);
        }

    }



    public function getAllFromVcard(Vcard $vcard){
        //this error response is not working yet
        if(!$vcard){
            return response()->json([
                'status' => 'error',
                'message' => 'vCard not found'
            ], 404);
        }
        return $vcard->categories;
    }

    public function getMyCategoriesDAD(){

        $vcard = Auth::user();

        $categories = $vcard->categories;

        return $this->responseService->sendWithDataResponse(200, null, ['categories' => $categories]);
    }

    public function getMyCategories(){
        $vcard = Auth::user();

        return response()->json([
            'status' => 'success',
            'data' => $vcard->categories,
        ], 200);
    }

    public function destroyCategoriesDAD(int $id){

        $validator = Validator::make(['id' => $id], [
            'id' => 'required',
        ]);

        if($validator->fails()){
            return $this->errorService->sendValidatorError(422, "Validation Failed", $validator->errors());
        }

        $category = DefaultCategory::find($id);

        if(!$category){
            return $this->errorService->sendStandardError(404, "Category not found");
        }

        $category->delete();

        return $this->responseService->sendStandardResponse(200, "Category deleted successfully");
    }

    public function destroyMyCategoriesDAD(int $id){

        $validator = Validator::make(['id' => $id], [
            'id' => 'required',
        ]);

        if($validator->fails()){
            return $this->errorService->sendValidatorError(422, "Validation Failed", $validator->errors());
        }

        $category = Category::find($id);

        if(!$category){
            return $this->errorService->sendStandardError(404, "Category not found");
        }

        $category->delete();

        return $this->responseService->sendStandardResponse(200, "Category deleted successfully");
    }






}
