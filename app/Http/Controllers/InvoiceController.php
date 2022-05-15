<?php

namespace App\Http\Controllers;
// namespace App\Http\Controllers\Validator;

use App\Invoice;
use App\Invoice_attachment;
use App\InvoiceDetails;
use App\Section;
use App\User;
use  App\Notifications;
use App\Exports\InvoicesExport;
use App\Notifications\addInvoices;
use Maatwebsite\Excel\Facades\Excel;
use App\Notifications\InvoiceMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;



class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    
    function __construct()
    {

        $this->middleware('permission:قائمة الفواتير', ['only' => ['index']]);
        $this->middleware('permission:اضافة فاتورة', ['only' => ['create','store']]);
        $this->middleware('permission:تعديل الفاتورة', ['only' => ['edit','update']]);
        $this->middleware('permission:حذف الفاتورة', ['only' => ['destroy']]);
        $this->middleware('permission:الفواتير المدفوعة', ['only' => ['Invoice_Paid']]);
        $this->middleware('permission:الفواتير الغير مدفوعة', ['only' => ['Invoice_UnPaid']]);
        $this->middleware('permission:الفواتير المدفوعة جزئيا', ['only' => ['Invoice_Partial']]);

    }
    public function index()
    {
        $invoices = Invoice::all();
        return view('invioces.invioce' ,compact('invoices'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Responses
     */
    public function create()
    {
        $sections = Section::all();
        return view('invioces.add_invoice' , compact('sections'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    { 
        // $validator = Validator::make($request->all(),$rules ,$messages);
        // if($validator->fails()){
        //     return redirect()->back()->withErrors($validator)->withInputs($request->all());
        // }
        $rules = $this->getRules(); 
        $messages = $this->getMessages(); 
        $request->validate( $rules ,$messages);

        
        $data = $request->all();
        $data['section_id'] = $request->Section;
        $data['Status'] = 'غير مدفوعة';
        $data['Value_Status'] = 2 ;
        Invoice::create($data);


        $invoice_id = Invoice::latest()->first()->id;
        $data_details = $request->all();
        $data_details['id_Invoice']    = $invoice_id ; 
        $data_details['Status']        = 'غير مدفوعة';
        $data_details['Value_Status']  = 2 ; 
        $data_details['user']          = (Auth::user()->name); 
        InvoiceDetails::create($data_details);


        if ($request->hasFile('pic')) {

            $invoice_id = Invoice::latest()->first()->id;
            $image = $request->file('pic');
            $file_name = $image->getClientOriginalName();
            $invoice_number = $request->invoice_number;

            $attachments = new Invoice_attachment();
            $attachments->file_name = $file_name;
            $attachments->invoice_number = $invoice_number;
            $attachments->Created_by = Auth::user()->name;
            $attachments->invoice_id = $invoice_id;
            $attachments->save();

            // move pic
            $imageName = $request->pic->getClientOriginalName();
            $request->pic->move(public_path('Attachments/' . $invoice_number), $imageName);
        }

        // $users_mail = Auth::user();
        // Notification::send($users_mail, new InvoiceMail($invoice_id));

        $user = User::get();
        $invoices = Invoice::latest()->first();
        Notification::send($user, new addInvoices($invoices));

        session()->flash('success' , ' تم اضافة لفاتورة بنجاح');
        return redirect()->back();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $invoices = Invoice::where('id', $id)->first();
        return view('invioces.status_update', compact('invoices'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $invoices = Invoice::find($id);
        $sections  = Section::all();

        return view('invioces.edit_invoice', compact('invoices','sections'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request , $id)
    {
        $request->validate([
            'pic' => 'mimes:pdf,jpeg,png,jpg',
            'invoice_number' => 'required|max:50|unique:invoices,invoice_number,'. $id,
            'product'=> 'required|max:50',
            'Amount_collection'=> 'max:8',
            'Amount_Commission'=> 'max:8',
            'Discount'=> 'max:8',
            'Value_VAT'=> 'max:8',
            'Rate_VAT'=> 'max:999',
            'Total'=> 'max:8',
        ],[
            'pic.mimes' => 'صيغة المرفق يجب ان تكون   pdf, jpeg , png , jpg',
            'invoice_number.required' =>'يرجى ملا هذا المحتوى',
            'invoice_number.unique' => 'اسم الفاتورة مكرر',
            'invoice_number.max' => 'الرقم لا يزيد عن 50',
            'product.required'=> 'يرجى ملا هذا المحتوى',
            'product.max' => ' المنتج لا يزيد عن 50 حرف',
            'Amount_collection.max'=> ' الرقم لا يزيد عن 8',
            'Amount_Commission.max'=> ' الرقم لا يزيد عن 8',
            'Discount.max'=> ' الرقم لا يزيد عن 8',
            'Value_VAT.max'=> ' الرقم لا يزيد عن 8',
            'Rate_VAT.max'=> ' الرقم لا يزيد عن 999',
            'Total.max'=> ' الرقم لا يزيد عن 8',
        ]);

        $data = $request->all();
        $data['section_id'] = $request->Section;
        $invoices = Invoice::findOrFail($id);
        $invoices->update($data);

        

        session()->flash('edit', 'تم تعديل الفاتورة بنجاح');
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $id =  $request->invoice_id;
        $invoices = Invoice::where('id', $id)->first();
        $Details = Invoice_attachment::where('invoice_id', $id)->first();

        $id_page =$request->id_page;


        if (!$id_page==2) {

        if (!empty($Details->invoice_number)) {

            Storage::disk('public_uploads')->deleteDirectory($Details->invoice_number);
        }

            $invoices->forceDelete();
            session()->flash('delete_invoice');
            return redirect('/invioces');

        }

        else {

            $invoices->delete();
            session()->flash('archive_invoice');
            return redirect('/InvoiceArchive');
        }


    }

    public function getRules(){
        return $rules = [
            'pic' => 'mimes:pdf,jpeg,png,jpg',
            'invoice_number' => 'required|unique:invoices,invoice_number|max:50',
            'product'=> 'required|max:50',
            'Amount_collection'=> 'max:8',
            'Amount_Commission'=> 'max:8',
            'Discount'=> 'max:8',
            'Value_VAT'=> 'max:8',
            'Rate_VAT'=> 'max:999',
            'Total'=> 'max:8',
        ];
    }
    public function getMessages(){
        return $messages = [
            'pic.mimes' => 'صيغة المرفق يجب ان تكون   pdf, jpeg , png , jpg',
            'invoice_number.required' =>'يرجى ملا هذا المحتوى',
            'invoice_number.unique' => 'اسم الفاتورة مكرر',
            'invoice_number.max' => 'الرقم لا يزيد عن 50',
            'product.required'=> 'يرجى ملا هذا المحتوى',
            'product.max' => ' المنتج لا يزيد عن 50 حرف',
            'Amount_collection.max'=> ' الرقم لا يزيد عن 8',
            'Amount_Commission.max'=> ' الرقم لا يزيد عن 8',
            'Discount.max'=> ' الرقم لا يزيد عن 8',
            'Value_VAT.max'=> ' الرقم لا يزيد عن 8',
            'Rate_VAT.max'=> ' الرقم لا يزيد عن 999',
            'Total.max'=> ' الرقم لا يزيد عن 8',
        ];
    }

    public function getproducts($id)
    {
        $products = DB::table("products")->where("section_id", $id)->pluck("Product_name", "id");
        return json_encode($products);
    }

    public function Status_Update($id, Request $request)
    {
        $invoices = Invoice::findOrFail($id);

        if ($request->Status === 'مدفوعة') {

            $invoices->update([
                'Value_Status' => 1,
                'Status' => $request->Status,
                'Payment_Date' => $request->Payment_Date,
            ]);

            InvoiceDetails::create([
                'id_Invoice' => $request->invoice_id,
                'invoice_number' => $request->invoice_number,
                'product' => $request->product,
                'Section' => $request->Section,
                'Status' => $request->Status,
                'Value_Status' => 1,
                'note' => $request->note,
                'Payment_Date' => $request->Payment_Date,
                'user' => (Auth::user()->name),
            ]);
        }

        else {
            $invoices->update([
                'Value_Status' => 3,
                'Status' => $request->Status,
                'Payment_Date' => $request->Payment_Date,
            ]);
            InvoiceDetails::create([
                'id_Invoice' => $request->invoice_id,
                'invoice_number' => $request->invoice_number,
                'product' => $request->product,
                'Section' => $request->Section,
                'Status' => $request->Status,
                'Value_Status' => 3,
                'note' => $request->note,
                'Payment_Date' => $request->Payment_Date,
                'user' => (Auth::user()->name),
            ]);
        }
        session()->flash('Status_Update');
        return redirect('/invioces');

    }


    public function Invoice_Paid()
    {
        $invoices = Invoice::where('Value_Status', 1)->get();
        return view('invioces.invoices_paid',compact('invoices'));
    }

    public function Invoice_UnPaid()
    {
        $invoices = Invoice::where('Value_Status',2)->get();
        return view('invioces.invoices_unpaid',compact('invoices'));
    }

    public function Invoice_Partial()
    {
        $invoices = Invoice::where('Value_Status',3)->get();
        return view('invioces.invoices_Partial',compact('invoices'));
    }

    public function Print_invoice($id)
    {
        $invoices = Invoice::where('id', $id)->first();
        return view('invioces.Print_invoice',compact('invoices'));
    }

    public function export() 
    {
        return Excel::download(new InvoicesExport, 'invoices.xlsx');
    }

    
    public function MarkAsRead_all ()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return  redirect()->back();

    }

    public function markAsRead(){

      auth()->user()->notifications->markAsRead();
    
}



   

}
