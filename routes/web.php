<?php

use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Auth;




/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('auth.login');
});


// Auth::routes();
Auth::routes(['register' => false]);


Route::get('/home', 'HomeController@index')->name('home');

Route::resource('/invioces', 'InvoiceController')->middleware('auth');

Route::resource('/invioce_details', 'InvoiceDetailsController')->middleware('auth');

Route::get('/section/{id}', 'InvoiceController@getproducts');   // ajax request data

Route::get('Invoice_Paid','InvoiceController@Invoice_Paid');

Route::get('Invoice_UnPaid','InvoiceController@Invoice_UnPaid');

Route::get('Invoice_Partial','InvoiceController@Invoice_Partial');

Route::post('/Status_Update/{id}', 'InvoiceController@Status_Update')->name('Status_Update');

Route::get('Print_invoice/{id}','InvoiceController@Print_invoice');

Route::get('export_invoices', 'InvoiceController@export');


Route::resource('/sections', 'SectionController')->middleware('auth');

Route::resource('/products', 'ProductController')->middleware('auth');


Route::get('download/{invoice_number}/{file_name}', 'InvoiceDetailsController@get_file');

Route::get('View_file/{invoice_number}/{file_name}', 'InvoiceDetailsController@open_file');

Route::post('delete_file', 'InvoiceDetailsController@destroy')->name('delete_file');

Route::resource('InvoiceAttachments', 'InvoiceAttachmentController');

Route::resource('InvoiceArchive', 'InvoicesArchiveController');

Route::group(['middleware' => ['auth']], function() {

    Route::resource('roles','RoleController');
    
    Route::resource('users','UserController');
    
});

Route::get('/report_invoices' ,'ReportController@index');

Route::post('/search_invoices' ,'ReportController@search_invoice');

Route::get('/report_customers' ,'Custom_ReportController@index');

Route::post('/Search_customers' ,'Custom_ReportController@search_customers');

Route::get('/MarkAsRead_all','InvoiceController@MarkAsRead_all')->name('MarkAsRead_all');

Route::get('/markAsRead','InvoiceController@markAsRead')->name('markAsRead');

Route::get('/unreadNotifications_count', 'InvoiceController@unreadNotifications_count')->name('unreadNotifications_count');

Route::get('/unreadNotifications', 'InvoiceController@unreadNotifications')->name('unreadNotifications');

Route::get('/clear', function() {
    Artisan::call('cache:clear');
    Artisan::call('config:cache');
    Artisan::call('view:clear');
    return "Cleared!";
});




Route::get('/{page}', 'AdminController@index')->middleware('auth');
