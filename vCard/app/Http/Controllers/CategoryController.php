<?php

namespace App\Http\Controllers;

use App\Models\DefaultCategory;
use App\Models\Vcard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function index(){
        $categories = DefaultCategory::all();
        return $categories;
    }

    public function getAllFromVcard(Vcard $vcard){
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
