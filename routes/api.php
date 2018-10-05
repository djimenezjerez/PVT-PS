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
Route::get('loans_command','LoanController@loans_command');
Route::get('loans_in_arrears','LoanController@loans_in_arrears');
Route::get('loans_senasir_report','LoanReportController@loans_senasir_report');
Route::get('loans_command_report','LoanReportController@loans_command_report');
Route::get('activos_cancelados','LoanReportController@activos_cancelados');
Route::get('loans_pasivo_mora_report','LoanReportController@loans_pasivo_mora_report');
Route::get('loans_activo_mora_report','LoanReportController@loans_activo_mora_report');
Route::resource('amortizacion','AmortizationController');
Route::resource('partial_loans','PartialLoansController');
Route::resource('overdue_loans','OverdueLoansController');
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
