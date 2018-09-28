<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Log;
use DB;
class AmortizationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // ini_set ('max_execution_time', 36000); 
        // // aumentar el tamaño de memoria permitido de este script: 
        // ini_set ('memory_limit', '960M');
        switch(request('sorted'))
        {
            case 'PresNumero': 
            case 'PresFechaDesembolso': 
                $sorted = 'Prestamos.'.request('sorted');
                break;
            case 'AmrFecPag':
            case 'AmrFecTrn':
            case 'AmrCap':
            case 'AmrInt':
            case 'AmrIntPen':
            case 'AmrOtrCob':
            case 'AmrTotPag':
            case 'AmrTipPAgo':
            case 'AmrNroCpte':
                $sorted = 'Amortizacion.'.request('sorted');
                break;
            case 'PadTipo':
            case 'PadNombres':
            case 'PadNombres2do':
            case 'PadPaterno':
            case 'PadMaterno':
            case 'PadCedulaIdentidad':
            case 'PadExpCedula':
                $sorted = 'Padron.'.request('sorted');
                break;
        }
   
        $order = request('order');
        $PresNumero = request('PresNumero')??'';
        $AmrFecPag = request('AmrFecPag')??'';
        $amortizaciones = DB::table('Prestamos')
                            ->join('Amortizacion','Prestamos.IdPrestamo','=','Amortizacion.IdPrestamo')
                            ->join('Padron','Prestamos.IdPadron','=','Padron.IdPadron')
                            ->where('Prestamos.PresNumero','like',"%{$PresNumero}%")
                            ->orWhere('Amortizacion.AmrFecPag','=',$AmrFecPag)
                            ->where('Prestamos.PresEstPtmo','=','V')
                            ->where('Prestamos.PresSaldoAct','>',0)
                            ->where('Padron.PadTipo','=','ACTIVO')
                            ->where('Amortizacion.AmrSts','!=','X')
                            // ->where('Amortizacion.AmrFecPag','=','2018-07-31')
                            ->select('Prestamos.PresNumero','Prestamos.PresFechaDesembolso',
                                     'Padron.IdPadron',
                                     'Amortizacion.AmrFecPag', 'Amortizacion.AmrFecTrn','Amortizacion.AmrCap','Amortizacion.AmrInt','Amortizacion.AmrIntPen','Amortizacion.AmrOtrCob','Amortizacion.AmrTotPag','Amortizacion.AmrTipPAgo' ,'Amortizacion.AmrNroCpte'
                                    )
                            ->orderBy($sorted,$order)
                            ->paginate(10);

        $amortizaciones->getCollection()->transform(function ($item) {
         
            $padron = DB::table('Padron')->where('IdPadron',$item->IdPadron)->first();
            $item->PadTipo = utf8_encode(trim($padron->PadTipo));
            $item->PadNombres = utf8_encode(trim($padron->PadNombres));
            $item->PadNombres2do =utf8_encode(trim($padron->PadNombres2do));
            $item->PadPaterno =utf8_encode(trim($padron->PadPaterno));
            $item->PadMaterno =utf8_encode(trim($padron->PadMaterno));
            $item->PadCedulaIdentidad =utf8_encode(trim($padron->PadCedulaIdentidad));
            $item->PadExpCedula =utf8_encode(trim($padron->PadExpCedula));
         
            return $item;
        });

        return response()->json($amortizaciones->toArray());
    
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
