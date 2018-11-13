<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Log;
use DB;
class ComandController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        //
        //
        // aumenta el tiempo máximo de ejecución de este script a 150 min: 
        ini_set ('max_execution_time', 9000); 
        // aumentar el tamaño de memoria permitido de este script: 
        ini_set ('memory_limit', '960M');
        
        $excel = request('excel')??'';
        $order = request('order')??'';
        $pagination_rows = request('pagination_rows')??10;
        $conditions = [];
        //filtros
        $PresNumero = request('PresNumero')??'';
        $PresFechaDesembolso = request('PresFechaDesembolso')??'';
        $PadCedulaIdentidad = request('PadCedulaIdentidad')??'';
        $PadNombres = request('PadNombres')??'';
        $PadNombres2do = request('PadNombres2do')??'';
        $PadPaterno = request('PadPaterno')??'';
        $PadMaterno = request('PadMaterno')??'';
        $PadTipo = request('PadTipo')??'';
        $PadExpCedula = request('PadExpCedula')??'';
        $PadMatricula = request('PadMatricula')??'';
        $PadMatriculaTit = request('PadMatriculaTit')??'';

        if($PresNumero != '')
        {
            array_push($conditions,array('Prestamos.PresNumero','like',"%{$PresNumero}%"));
        }
        if($PresFechaDesembolso != '')
        {
            $date_from = Carbon::parse($PresFechaDesembolso);
            $date_to = Carbon::parse($PresFechaDesembolso);
            $date_to->hour = 23;
            $date_to->minute = 59;
            $date_to->second = 59;
            array_push($conditions,array('Prestamos.PresFechaDesembolso','<=',$date_to));
            array_push($conditions,array('Prestamos.PresFechaDesembolso','>=',$date_from));
        }

        if($PadMatricula != '')
        {
            array_push($conditions,array('Padron.PadMatricula','like',"%{$PadMatricula}%"));
        }
        if($PadMatriculaTit != '')
        {
            array_push($conditions,array('Padron.PadMatriculaTit','like',"%{$PadMatriculaTit}%"));
        }
        if($PadExpCedula != '')
        {
            array_push($conditions,array('Padron.PadExpCedula','like',"%{$PadExpCedula}%"));
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
        
        // Log::info($PresFechaDesembolso);
        // $pres = DB::table('Prestamos')->where('PresFechaDesembolso','=',$PresFechaDesembolso)->first();
        // Log::info(json_encode($pres));
        if($excel!='')//reporte excel hdp 
        {
            global $rows_exacta;
            $rows_exacta = Array();
            //cabezera
            array_push($rows_exacta,array('Nro Prestamo','Fecha de Solicitud','Fecha Desembolso','Monto Desembolsado','Saldo Actual','Nro Comprobante','Ampliacion',
                                        'Producto',
                                        'Tipo','Matricula','Matricula Titular',' CI','Extension','1er Nombre','2do Nombre','Paterno','Materno', 'Apellido de Casada'));

                $loans = DB::table('Prestamos')
                            ->join('Padron','Prestamos.IdPadron','=','Padron.IdPadron')
                            ->join('Producto','Producto.PrdCod','=','Prestamos.PrdCod')
                            ->where($conditions)
                            ->where('Padron.PadTipo','=','ACTIVO')
                            ->where('Prestamos.PresEstPtmo','=','V')
                            ->where('Prestamos.PresSaldoAct','>',0)
                            ->select('Prestamos.IdPrestamo','Prestamos.PresNumero','Prestamos.PresFechaDesembolso','Prestamos.PresFechaPrestamo','Prestamos.PresCtbNroCpte','Prestamos.PresAmp','Prestamos.PresSaldoAct','Prestamos.PresMntDesembolso',
                                            'Padron.IdPadron',
                                            'Producto.PrdDsc'
                                            )
                            ->orderBy('Prestamos.PresNumero')
                            ->get();

            foreach($loans as $loan)
            {

                    $padron = DB::table('Padron')->where('IdPadron',$loan->IdPadron)->first();
                    $loan->PadTipo = utf8_encode(trim($padron->PadTipo));
                    $loan->PadNombres = utf8_encode(trim($padron->PadNombres));
                    $loan->PadNombres2do =utf8_encode(trim($padron->PadNombres2do));
                    $loan->PadPaterno =utf8_encode(trim($padron->PadPaterno));
                    $loan->PadMaterno =utf8_encode(trim($padron->PadMaterno));
                    $loan->PadApellidoCasada =utf8_encode(trim($padron->PadApellidoCasada));
                    $loan->PadCedulaIdentidad =utf8_encode(trim($padron->PadCedulaIdentidad));
                    $loan->PadExpCedula =utf8_encode(trim($padron->PadExpCedula));
                    $loan->PadMatricula =utf8_encode(trim($padron->PadMatricula));
                    $loan->PadMatriculaTit =utf8_encode(trim($padron->PadMatriculaTit));

                    array_push($rows_exacta,array($loan->PresNumero,$loan->PresFechaPrestamo,$loan->PresFechaDesembolso,$loan->PresMntDesembolso,$loan->PresSaldoAct,$loan->PresCtbNroCpte,$loan->PresAmp,
                                                $loan->PrdDsc,
                                                $loan->PadTipo,$loan->PadMatricula,$loan->PadMatriculaTit,$loan->PadCedulaIdentidad, $loan->PadExpCedula,$loan->PadNombres,$loan->PadNombres2do,$loan->PadPaterno,$loan->PadMaterno,$loan->PadApellidoCasada
                                            ));    
            }
            Excel::create('prestamos',function($excel)
            {
                global $rows_exacta;
                
                        $excel->sheet('prestamos vigentes',function($sheet) {
                                global $rows_exacta;
                
                                $sheet->fromModel($rows_exacta,null, 'A1', false, false);
                                $sheet->cells('A1:R1', function($cells) {
                                // manipulate the range of cells
                                $cells->setBackground('#058A37');
                                $cells->setFontColor('#ffffff');  
                                $cells->setFontWeight('bold');
                                });
                            });
    
                      
            })->download('xls');

        }else{

            $loans = DB::table('Prestamos')
                        ->join('Padron','Prestamos.IdPadron','=','Padron.IdPadron')
                        ->join('Producto','Producto.PrdCod','=','Prestamos.PrdCod')
                        ->where($conditions)
                        ->where('Prestamos.PresEstPtmo','=','V')
                        ->where('Prestamos.PresSaldoAct','>',0)
                        ->select('Prestamos.IdPrestamo','Prestamos.PresNumero','Prestamos.PresFechaDesembolso','Prestamos.PresFechaPrestamo','Prestamos.PresCtbNroCpte','Prestamos.PresAmp',
                                        'Padron.IdPadron',
                                        'Producto.PrdDsc'
                                        )
                        ->orderBy('Prestamos.PresFechaDesembolso','Desc')
                        ->paginate($pagination_rows);

            $loans->getCollection()->transform(function ($item) {
                $padron = DB::table('Padron')->where('IdPadron',$item->IdPadron)->first();
                $item->PadTipo = utf8_encode(trim($padron->PadTipo));
                $item->PadNombres = utf8_encode(trim($padron->PadNombres));
                $item->PadNombres2do =utf8_encode(trim($padron->PadNombres2do));
                $item->PadPaterno =utf8_encode(trim($padron->PadPaterno));
                $item->PadMaterno =utf8_encode(trim($padron->PadMaterno));
                $item->PadCedulaIdentidad =utf8_encode(trim($padron->PadCedulaIdentidad));
                $item->PadExpCedula =utf8_encode(trim($padron->PadExpCedula));
                $item->PadMatricula =utf8_encode(trim($padron->PadMatricula));
                $item->PadMatriculaTit =utf8_encode(trim($padron->PadMatriculaTit));
                return $item;
            });

            return response()->json($loans->toArray());
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
