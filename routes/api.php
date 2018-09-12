<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::resource('reporte','LoanReportController');
Route::get('reporte_prestamos','LoanReportController@Loans');
Route::get('negative_loans','LoanController@negative_loans');
Route::get('loans_senasir','LoanController@loans_senasir');
Route::get('loans_senasir_report','LoanReportController@loans_senasir_report');
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
