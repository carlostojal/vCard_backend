<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use App\Models\DefaultCategory;
use App\Models\Vcard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function index(){
        $categories = DefaultCategory::paginate(15);
        return response()->json([
            'status' => 'success',
            'data' => $categories,
            'lastPage' => $categories->lastPage(),
        ]);
        // return $categories;
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

    public function show(String $query, Request $request){

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

    public function getMyCategories(){
        $vcard = Auth::user();
        return response()->json([
            'status' => 'success',
            'data' => $vcard->categories,
        ], 200);
    }
}
