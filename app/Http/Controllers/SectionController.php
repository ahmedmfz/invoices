<?php

namespace App\Http\Controllers;

use App\Section;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    function __construct()
    {
    
    $this->middleware('permission:الاقسام', ['only' => ['index']]);
    $this->middleware('permission:اضافة قسم', ['only' => ['create','store']]);
    $this->middleware('permission:تعديل قسم', ['only' => ['edit','update']]);
    $this->middleware('permission:حذف قسم', ['only' => ['destroy']]);
    
    }

    public function index()
    {
        $sections = Section::all();
        return view('sections.section', compact('sections'));
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
        
        $request->validate([
            'section_name' => 'required|string|unique:sections,section_name|max:255',
        ],[

            'section_name.required' =>'يرجي ادخال اسم القسم',
            'section_name.unique' =>'اسم القسم مسجل مسبقا',

        ]);

        $data = $request->all();
     
        $data['created_by'] = Auth()->user()->name;
        $target_data = section::create($data);
        $target_data->save();

        session()->flash('add' , 'تم اضافة قسم جديد');
        return redirect('sections');
        
        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Section  $section
     * @return \Illuminate\Http\Response
     */
    public function show(Section $section)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Section  $section
     * @return \Illuminate\Http\Response
     */
    public function edit(Section $section)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Section  $section
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $id = $request->id;
      
        $this->validate($request, [

            'section_name' => 'required|max:255|unique:Sections,section_name,'.$id,
        ],[

            'section_name.required' =>'يرجي ادخال اسم القسم',
            'section_name.unique' =>'اسم القسم مسجل مسبقا',

        ]);

        $data = $request->all();
        $sections = section::find($id);
        $sections->update( $data);

        session()->flash('edit','تم تعديل القسم بنجاج');
        return redirect('/sections');
    
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Section  $section
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $id = $request->id;
        section::find($id)->delete();
        session()->flash('delete','تم حذف القسم بنجاح');
        return redirect('/sections');
    }
}
