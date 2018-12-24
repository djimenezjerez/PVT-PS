<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Log;
use DB;
use Carbon\Carbon;
class AmortizationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // return 'brian y tati';
         // aumenta el tiempo máximo de ejecución de este script a 150 min: 
         ini_set ('max_execution_time', 9000); 
         // aumentar el tamaño de memoria permitido de este script: 
         ini_set ('memory_limit', '960M');
        // switch(request('sorted'))
        // {
        //     case 'PresNumero': 
        //     case 'PresFechaDesembolso': 
        //         $sorted = 'Prestamos.'.request('sorted');
        //         break;
        //     case 'AmrFecPag':
        //     case 'AmrFecTrn':
        //     case 'AmrCap':
        //     case 'AmrInt':
        //     case 'AmrIntPen':
        //     case 'AmrOtrCob':
        //     case 'AmrTotPag':
        //     case 'AmrTipPAgo':
        //     case 'AmrNroCpte':
        //         $sorted = 'Amortizacion.'.request('sorted');
        //         break;
        //     case 'PadTipo':
        //     case 'PadNombres':
        //     case 'PadNombres2do':
        //     case 'PadPaterno':
        //     case 'PadMaterno':
        //     case 'PadCedulaIdentidad':
        //     case 'PadExpCedula':
        //         $sorted = 'Padron.'.request('sorted');
        //         break;
        // }
        
        $excel = request('excel')??'';
        $order = request('order')??'';
        $pagination_rows = request('pagination_rows')??10;
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
        $AmrTipPAgo = request('AmrTipPAgo')??'';
        $AmrSts = request('AmrSts')??'';
        
        $conditions = [];
        if($PresNumero != '')
        {
            array_push($conditions,array('Prestamos.PresNumero','like',"%{$PresNumero}%"));
        }
        if($AmrFecPag != '')
        {
            $date_from = Carbon::parse($AmrFecPag);
            $date_to = Carbon::parse($AmrFecPag);
            $date_to->hour = 23;
            $date_to->minute = 59;
            $date_to->second = 59;
            array_push($conditions,array('Amortizacion.AmrFecPag','<=',$date_to));
            array_push($conditions,array('Amortizacion.AmrFecPag','>=',$date_from));

        }
        if($AmrTipPAgo != '')
        {
            array_push($conditions,array('Amortizacion.AmrTipPAgo','=',$AmrTipPAgo));
        }
        if($AmrSts != '')
        {
            array_push($conditions,array('Amortizacion.AmrSts','=',$AmrSts));
        }
        if($AmrNroCpte != '')
        {
            array_push($conditions,array('Amortizacion.AmrNroCpte','=',$AmrNroCpte));
        }
        if($AmrTipPAgo != '')
        {
            array_push($conditions,array('Amortizacion.AmrTipPAgo','like',"%{$AmrTipPAgo}%"));
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

        // Log::info($conditions);
        if($excel!='')//reporte excel hdp 
        {
            global $rows_exacta;
            $rows_exacta = Array();
            //cabezera
            array_push($rows_exacta,array('Prestamos.PresNumero','PresFechaDesembolso','Tipo','Fecha Pago','Fecha Transaccion','Estado Amortizacion','Producto','Padron.PadMatricula',' Padron.PadCedulaIdentidad',' Padron.PadPaterno','Padron.PadMaterno',' Padron.PadNombres','Padron.PadNombres2do', 'Capital','Interes','Interes penal','otros cobros','Amortizacion.AmrTotPag','Tipo Descuento','Numero Comprobante'));
            $amortizaciones = DB::table('Prestamos')
                            ->join('Amortizacion','Prestamos.IdPrestamo','=','Amortizacion.IdPrestamo')
                            ->join('Padron','Prestamos.IdPadron','=','Padron.IdPadron')
                            ->join('Producto','Producto.PrdCod','=','Prestamos.PrdCod')
                            ->where($conditions)
                            ->where('Prestamos.PresEstPtmo','=','V')
                            ->where('Prestamos.PresSaldoAct','>',0)
                            // ->where('Amortizacion.AmrSts','!=','X')
                            ->select('Prestamos.PresNumero','Prestamos.PresFechaDesembolso',
                                     'Padron.IdPadron',
                                     'Producto.PrdDsc',
                                     'Amortizacion.AmrFecPag', 'Amortizacion.AmrFecTrn','Amortizacion.AmrCap','Amortizacion.AmrInt','Amortizacion.AmrIntPen','Amortizacion.AmrOtrCob','Amortizacion.AmrTotPag','Amortizacion.AmrTipPAgo' ,'Amortizacion.AmrNroCpte','Amortizacion.AmrSts'
                                    )
                            ->orderBy('Prestamos.PresNumero')
                            ->get();

            foreach($amortizaciones as $amortizacion)
            {

                    $padron = DB::table('Padron')->where('IdPadron',$amortizacion->IdPadron)->first();
                    $amortizacion->PadTipo = utf8_encode(trim($padron->PadTipo));
                    $amortizacion->PadNombres = utf8_encode(trim($padron->PadNombres));
                    $amortizacion->PadNombres2do =utf8_encode(trim($padron->PadNombres2do));
                    $amortizacion->PadPaterno =utf8_encode(trim($padron->PadPaterno));
                    $amortizacion->PadMaterno =utf8_encode(trim($padron->PadMaterno));
                    $amortizacion->PadCedulaIdentidad =utf8_encode(trim($padron->PadCedulaIdentidad));
                    $amortizacion->PadExpCedula =utf8_encode(trim($padron->PadExpCedula));
                    $amortizacion->PadMatricula =utf8_encode(trim($padron->PadMatricula));

                    array_push($rows_exacta,array($amortizacion->PresNumero,$amortizacion->PresFechaDesembolso,$amortizacion->PadTipo,$amortizacion->AmrFecPag,$amortizacion->AmrFecTrn,$amortizacion->AmrSts,$amortizacion->PrdDsc,$amortizacion->PadMatricula,$amortizacion->PadCedulaIdentidad,$amortizacion->PadPaterno,$amortizacion->PadMaterno,$amortizacion->PadNombres,$amortizacion->PadNombres2do, $amortizacion->AmrCap,$amortizacion->AmrInt,$amortizacion->AmrIntPen,$amortizacion->AmrOtrCob,$amortizacion->AmrTotPag,$amortizacion->AmrTipPAgo,$amortizacion->AmrNroCpte));    
            }
            Excel::create('Amortizaciones',function($excel)
            {
                global $rows_exacta;
                
                        $excel->sheet('Amortizaciones',function($sheet) {
                                global $rows_exacta;
                
                                $sheet->fromModel($rows_exacta,null, 'A1', false, false);
                                $sheet->cells('A1:C1', function($cells) {
                                // manipulate the range of cells
                                $cells->setBackground('#058A37');
                                $cells->setFontColor('#ffffff');  
                                $cells->setFontWeight('bold');
                                });
                            });
    
                      
            })->download('xls');

        }else{

        
            //flujo normal
            $amortizaciones = DB::table('Prestamos')
                                ->join('Amortizacion','Prestamos.IdPrestamo','=','Amortizacion.IdPrestamo')
                                ->join('Padron','Prestamos.IdPadron','=','Padron.IdPadron')
                                ->where($conditions)
                                ->where('Prestamos.PresEstPtmo','=','V')
                                ->where('Prestamos.PresSaldoAct','>',0)
                               
                                // ->where('Amortizacion.AmrSts','!=','X')
                                ->select('Prestamos.PresNumero','Prestamos.PresFechaDesembolso',
                                        'Padron.IdPadron',
                                        'Amortizacion.AmrFecPag', 'Amortizacion.AmrFecTrn','Amortizacion.AmrCap','Amortizacion.AmrInt','Amortizacion.AmrIntPen','Amortizacion.AmrOtrCob','Amortizacion.AmrTotPag','Amortizacion.AmrTipPAgo' ,'Amortizacion.AmrNroCpte','Amortizacion.AmrSts'
                                        )
                                ->orderBy('Prestamos.PresNumero')
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
