<?php

namespace App\Http\Controllers;
use App\Invoice;
use App\Invoice_attachment;
use App\invoices_archive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
class InvoicesArchiveController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    function __construct()
    {

        $this->middleware('permission:ارشيف الفواتير', ['only' => ['index']]);
     

    }
    public function index()
    {
        $invoices = Invoice::onlyTrashed()->get();
        return view('invioces.Archive_Invoices'  , compact('invoices'));
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\invoices_archive  $invoices_archive
     * @return \Illuminate\Http\Response
     */
    public function show(invoices_archive $invoices_archive)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\invoices_archive  $invoices_archive
     * @return \Illuminate\Http\Response
     */
    public function edit(invoices_archive $invoices_archive)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\invoices_archive  $invoices_archive
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $id = $request->invoice_id;
        $flight = Invoice::withTrashed()->where('id', $id)->restore();
        session()->flash('restore_invoice');
        return redirect('/invioces');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\invoices_archive  $invoices_archive
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
         $invoices = Invoice::withTrashed()->where('id',$request->invoice_id)->first();
         $Details = Invoice_attachment::where('id',$request->invoice_id)->first();

         if (!empty($Details->invoice_number)) {

            Storage::disk('public_uploads')->deleteDirectory($Details->invoice_number);
        }
         $invoices->forceDelete();
         session()->flash('delete_invoice');
         return redirect('/InvoiceArchive');
    }
}
