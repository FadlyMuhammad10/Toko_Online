<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Category;
use App\Product;

class CategoryController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $categories= Category::all();
        $products= Product::with('galleries')->paginate(32);
        return view('pages.category',[
            'categories'=>$categories,
            'products'=>$products
        ]);
    }
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function detail(Request $Request ,$slug)
    {
        $categories= Category::all();
        $category = Category::where('slug',$slug)->firstOrFail();
        $products= Product::with('galleries')->where('categories_id',$category->id)->paginate(32);
        return view('pages.category',[
            'categories'=>$categories,
            'products'=>$products
        ]);
    }
}
