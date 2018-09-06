<?php

namespace App\Http\Controllers;

use App\Loan;
use Illuminate\Http\Request;
use DB;
use Log;
class LoanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        
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
     * @param  \App\Loan  $loan
     * @return \Illuminate\Http\Response
     */
    public function show(Loan $loan)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Loan  $loan
     * @return \Illuminate\Http\Response
     */
    public function edit(Loan $loan)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Loan  $loan
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Loan $loan)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Loan  $loan
     * @return \Illuminate\Http\Response
     */
    public function destroy(Loan $loan)
    {
        //
    }

    public function negative_loans()
    {
        // $loans = DB::table('Prestamos')
        //             ->join('Amortizacion','Prestamos.IdPrestamo','=','Amortizacion.IdPrestamo')
        //             ->where('Prestamos.PresEstPtmo','=','V')
        //             ->whereRaw('Amortizacion.AmrInt < 0 or Amortizacion.AmrIntPen < 0 or Amortizacion.AmrTotPag < 0 or Amortizacion.AmrSldAnt <0 or Amortizacion.AmrOtrCob <0')
        //             ->where('Amortizacion.AmrSts','!=','X')
        //             //->select('Prestamos.PresNumero',' Amortizacion.AmrInt',' Amortizacion.AmrIntPen','Amortizacion.AmrTotPag','Amortizacion.AmrSldAnt','Amortizacion.AmrOtrCob')
        //             ->groupBy('Prestamos.PresNumero',' Amortizacion.AmrInt',' Amortizacion.AmrIntPen','Amortizacion.AmrTotPag','Amortizacion.AmrSldAnt','Amortizacion.AmrOtrCob')
        //             ->get();
        $loans = DB::table('Prestamos')
                            ->join('Amortizacion','Amortizacion.IdPrestamo','=','Prestamos.IdPrestamo')
                            // ->where('PresEstPtmo','=','V')
                            ->whereRaw("Prestamos.PresEstPtmo = 'V' and (Amortizacion.AmrInt < 0 or Amortizacion.AmrIntPen < 0 or Amortizacion.AmrTotPag < 0 or Amortizacion.AmrSldAnt <0 or Amortizacion.AmrOtrCob <0)  and Amortizacion.AmrSts <> 'X'")
                            ->select('Prestamos.PresNumero','Prestamos.PresEstPtmo','Amortizacion.AmrInt','Amortizacion.AmrIntPen','Amortizacion.AmrTotPag','Amortizacion.AmrSldAnt','Amortizacion.AmrOtrCob')
                            ->groupBy('Prestamos.PresNumero','Prestamos.PresEstPtmo','Amortizacion.AmrInt','Amortizacion.AmrIntPen','Amortizacion.AmrTotPag','Amortizacion.AmrSldAnt','Amortizacion.AmrOtrCob')
                            //->take(100)
                            ->get();
       // return Response::json($loans);
        //Log::info(var_dump($loans));
        //Log::info(json_encode($loans));
       return json_encode($loans);
    }
}
