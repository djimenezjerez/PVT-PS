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
   
        $order = request('order'??'');
        $pagination_rows = request('pagination_rows'??10);
        $PresNumero = request('PresNumero')??'';
        $AmrFecPag = request('AmrFecPag')??'';
        $AmrTipPAgo = request('AmrTipPAgo')??'';
        $AmrNroCpte = request('AmrNroCpte')??'';
        $PadCedulaIdentidad = request('PadCedulaIdentidad')??'';
        $PadNombres = request('PadNombres')??'';
        $PadNombres2do = request('PadNombres2do')??'';
        $PadPaterno = request('PadPaterno')??'';
        $PadMaterno = request('PadMaterno')??'';
        $PadTipo = request('PadTipo')??'';
        
        $conditions = [];
        if($PresNumero != '')
        {
            array_push($conditions,array('Prestamos.PresNumero','like',"%{$PresNumero}%"));
        }
        if($AmrFecPag != '')
        {
            array_push($conditions,array('Amortizacion.AmrFecPag','=',$AmrFecPag));
        }
        if($AmrTipPAgo != '')
        {
            array_push($conditions,array('Amortizacion.AmrTipPAgo','=',$AmrTipPAgo));
        }
        if($AmrNroCpte != '')
        {
            array_push($conditions,array('Amortizacion.AmrNroCpte','=',$AmrNroCpte));
        }
        if($PadCedulaIdentidad != '')
        {
            array_push($conditions,array('Padron.PadCedulaIdentidad','like',"%{$PadCedulaIdentidad}%"));
        }
        if($PadNombres != '')
        {
            array_push($conditions,array('Padron.PadNombres','like',"%{$PadNombres}%"));
        }
        if($PadNombres2do != '')
        {
            array_push($conditions,array('Padron.PadNombres2do','like',"%{$PadNombres2do}%"));
        }
        if($PadPaterno != '')
        {
            array_push($conditions,array('Padron.PadPaterno','like',"%{$PadPaterno}%"));
        }
        if($PadMaterno != '')
        {
            array_push($conditions,array('Padron.PadMaterno','like',"%{$PadMaterno}%"));
        }
        if($PadTipo != '')
        {
            array_push($conditions,array('Padron.PadTipo','like',"%{$PadTipo}%"));
        }

        Log::info($conditions);

        $amortizaciones = DB::table('Prestamos')
                            ->join('Amortizacion','Prestamos.IdPrestamo','=','Amortizacion.IdPrestamo')
                            ->join('Padron','Prestamos.IdPadron','=','Padron.IdPadron')
                            ->where($conditions)
                            ->where('Prestamos.PresEstPtmo','=','V')
                            ->where('Prestamos.PresSaldoAct','>',0)
                            ->where('Padron.PadTipo','=','ACTIVO')
                            ->where('Amortizacion.AmrSts','!=','X')
                            ->select('Prestamos.PresNumero','Prestamos.PresFechaDesembolso',
                                     'Padron.IdPadron',
                                     'Amortizacion.AmrFecPag', 'Amortizacion.AmrFecTrn','Amortizacion.AmrCap','Amortizacion.AmrInt','Amortizacion.AmrIntPen','Amortizacion.AmrOtrCob','Amortizacion.AmrTotPag','Amortizacion.AmrTipPAgo' ,'Amortizacion.AmrNroCpte'
                                    )
                            ->orderBy($sorted,$order)
                            ->paginate($pagination_rows);

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
