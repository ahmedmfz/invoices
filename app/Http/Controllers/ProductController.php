<?php

namespace App\Http\Controllers;

use App\Product;

use App\Section;

use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    function __construct()
    {
    
    $this->middleware('permission:المنتجات', ['only' => ['index']]);
    $this->middleware('permission:اضافة منتج', ['only' => ['create','store']]);
    $this->middleware('permission:تعديل منتج', ['only' => ['edit','update']]);
    $this->middleware('permission:حذف منتج', ['only' => ['destroy']]);
    
    }

    public function index()
    {
        $sections = Section::all();
        $products = product::all();
        return view('products.product', compact('sections' ,'products'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // $b_exist = product::where('product_name' ,'=' , $data['section_id'])
        //                   ->where('section_id' , $data['product_name'])->exists();

        // if($b_exist){
        //     session()->flash('error', 'اسم المنتج مكرر فى القسم  مسبقا');
        //     return redirect('/products');
        // }
        // else{} 

        
            $request->validate([
                'product_name' => 'required|unique:products,product_name|max:255',
            ],[
                'product_name.required' =>'يرجي ادخال اسم المنتج',
                'product_name.unique' =>'اسم المنتج مسجل مسبقا',
            ]);

            $data = $request->all();
            Product::create($data);
            session()->flash('Add', 'تم اضافة المنتج بنجاح ');
            return redirect('/products');
         
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        // dd($request);
        $id_pro = $request->pro_id;
        $request->validate([
            'product_name' => 'required|max:255|unique:products,product_name,'.$id_pro ,
            
        ],[
            'product_name.required' =>'يرجي ادخال اسم المنتج',
            'product_name.unique' =>'اسم المنتج مسجل مسبقا',
        ]);



        $id = section::where('section_name', $request->section_name)->first()->id;       
        $Products = Product::findOrFail($request->pro_id);
        $Products->update([ 
            'product_name' => $request->product_name,
            'description' => $request->description,
            'section_id' => $id,
        ]);
 
        session()->flash('Edit', 'تم تعديل المنتج بنجاح');
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $Products = Product::findOrFail($request->pro_id);
        $Products->delete();
        session()->flash('delete', 'تم حذف المنتج بنجاح');
        return back();
    }
}
