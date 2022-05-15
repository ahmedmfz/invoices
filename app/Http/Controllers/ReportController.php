<?php

namespace App\Http\Controllers;
use App\Invoice;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index() {

        return view('reports.invoices_report');
    }
    public function search_invoice(request $request){

        $val = $request->radio;
        if($val == 1){

            if($request->type && $request->start_at == '' && $request->end_at == '' ){

                $target_data = Invoice::select('*')->where('status' ,'=', $request->type)->get();
                $type = $request->type;
                return view('reports.invoices_report',compact('type'))->withDetails($target_data);
            }
            else{
                $start_at = $request->start_at;
                $end_at   = $request->end_at;
                $type = $request->type;
                $target_data = Invoice::whereBetween('invoice_Date' ,[ $start_at , $end_at ])
                                       ->where('status' ,'=', $request->type)->get();
                return view('reports.invoices_report',compact('type','start_at','end_at'))->withDetails($target_data);

            }

        }
        else{
            $invoives = Invoice::where('invoice_number' , $request->invoice_number)->get();
            return view('reports.invoices_report')->withDetails($invoives);
        }
    }
}
