<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use DB;
use Log;
class LoanReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        return view('layout.app');
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

    public function Loans()
    {
        // aumenta el tiempo máximo de ejecución de este script a 150 min: 
        ini_set ('max_execution_time', 9000); 
        // aumentar el tamaño de memoria permitido de este script: 
        ini_set ('memory_limit', '960M');

        $loans = DB::table('Prestamos')->where('PresEstPtmo','=','V')->get();

        global $rows_exacta;
        $rows_exacta = array();
        array_push($rows_exacta,array('Prestamos.IdPrestamo',' Prestamos.PresNumero','PresFechaDesembolso','Prestamos.PresCuotaMensual'));
        foreach($loans as $loan)
        {
            $amortizaciones = DB::table('Amortizacion')->where('IdPrestamo','=',$loan->IdPrestamo)->whereRaw("AmrSts='X' and YEAR(AmrFecPag)=2018")->get();
            if(sizeof($amortizaciones)>0)
            {
                $saldo_anterior = $loan->PresMntDesembolso;
                // $saldo_actual = $amortizacion[0]->AmrSldAct;
                $sw = false;
                foreach($amortizaciones as $amortizacion)
                {
                    if($amortizacion->AmrSldAct>$saldo_anterior)
                    {
                        $sw = true;
                    }
                    $saldo_anterior = $amortizacion->AmrSldAct;
                }
                if($sw)
                {
                    array_push($rows_exacta,array($loan->IdPrestamo,$loan->PresNumero,$loan->PresFechaDesembolso,$loan->PresCuotaMensual));
                }

            }

            // array_push($rows_exacta,array($loan->IdPrestamo,$loan->PresNumero,$loan->PresFechaDesembolso,$loan->PresCuotaMensual));
        }

        Excel::create('prestamos',function($excel)
        {
            global $rows_exacta,$rows_not_found,$rows_desc_mayor,$rows_desc_menor,$rows_segundo_prestamo,$prestamos_noreg,$rows_gar;
            
                    $excel->sheet('Descuentos exactos',function($sheet) {
                            global $rows_exacta,$rows_not_found,$rows_desc_mayor,$rows_desc_menor;
            
                            $sheet->fromModel($rows_exacta,null, 'A1', false, false);
                            $sheet->cells('A1:C1', function($cells) {
                            // manipulate the range of cells
                            $cells->setBackground('#058A37');
                            $cells->setFontColor('#ffffff');  
                            $cells->setFontWeight('bold');
                            });
                        });
                  
        })->download('xls');
    }
    public function loans_senasir_report()
    {
        $loans =DB::table('Prestamos')->leftJoin('Padron','Padron.IdPadron','=','Prestamos.IdPadron')
                                        ->where('Prestamos.PresEstPtmo','=','V')
                                        ->where('Prestamos.PresSaldoAct','>',0)
                                        ->where('Padron.PadTipo','=','PASIVO')
                                        ->where('Padron.PadTipRentAFPSENASIR','=','SENASIR')
                                        ->select('Prestamos.IdPrestamo','Prestamos.PresFechaDesembolso','Prestamos.PresNumero','Prestamos.PresCuotaMensual','Prestamos.PresSaldoAct','Padron.PadTipo','Padron.PadCedulaIdentidad','Padron.PadNombres','Padron.PadNombres2do','Padron.IdPadron','Padron.PadMatricula','Prestamos.SolEntChqCod')
                                      //  ->take(40)
                                        ->get();
    
        global $prestamos;
        $prestamos =[ array('FechaDesembolso','Numero','Cuota','SaldoActual','Tipo','MatriculaTitular','MatriculaDerechohabiente','CI','Extension','PrimerNombre','SegundoNombre','Paterno','Materno','Frecuencia','Descuento','ciudad')];

        foreach($loans as $loan)
        {
        $padron = DB::table('Padron')->where('IdPadron','=',$loan->IdPadron)->first();

        // $loan->PresNumero = utf8_encode(trim($padron->PresNumero));
        $loan->PadNombres = utf8_encode(trim($padron->PadNombres));
        $loan->PadNombres2do =utf8_encode(trim($padron->PadNombres2do));
        $loan->PadPaterno =utf8_encode(trim($padron->PadPaterno));
        $loan->PadMaterno =utf8_encode(trim($padron->PadMaterno));
        $loan->PadMatriculaTit =utf8_encode(trim($padron->PadMatriculaTit));
        $loan->PadExpCedula =utf8_encode(trim($padron->PadExpCedula));

        $amortizacion = DB::table('Amortizacion')->where('IdPrestamo','=',$loan->IdPrestamo)->where('AmrSts','!=','X')->get();
        $departamento = DB::table('Departamento')->where('DepCod','=',$loan->SolEntChqCod)->first();
        if($departamento)
        {

            $loan->City =$departamento->DepDsc; 
        }else{
            $loan->City = '';
        }
        if(sizeof($amortizacion)>0)
        {
            $loan->State = 'Recurrente';
            
            if($loan->PresSaldoAct < $loan->PresCuotaMensual)
            {
                $loan->Discount = $loan->PresSaldoAct;
            }else
            {
                $loan->Discount = $loan->PresCuotaMensual;
            }

        }else{
            $loan->State = 'Nuevo';
            $plan_de_pago = DB::table('PlanPagosPlan')->where('IdPrestamo','=',$loan->IdPrestamo)->where('IdPlanNroCouta','=',1)->first();
            $loan->Discount = $plan_de_pago->PlanCuotaMensual;
        }



        //Log::info(json_encode($padron));
        array_push($prestamos,array(
                $loan->PresFechaDesembolso,
                $loan->PresNumero,
                $loan->PresCuotaMensual,
                $loan->PresSaldoAct,
                $loan->PadTipo,
                $loan->PadMatriculaTit,
                $loan->PadMatricula,
                $loan->PadCedulaIdentidad,
                $loan->PadExpCedula,
                $loan->PadNombres,
                $loan->PadNombres2do,
                $loan->PadPaterno,
                $loan->PadMaterno,
                $loan->State,
                $loan->Discount,
                $loan->City,
        ));
        }

        Excel::create('prestamos altas senasir',function($excel)
        {
            global $prestamos;
            
                    $excel->sheet('presamos vigentes',function($sheet) {
                            global $prestamos;
                            $sheet->fromModel($prestamos,null, 'A1', false, false);
                            $sheet->cells('A1:N1', function($cells) {
                            // manipulate the range of cells
                            $cells->setBackground('#058A37');
                            $cells->setFontColor('#ffffff');  
                            $cells->setFontWeight('bold');
                            });
                        });
                  
        })->download('xls');
               
    }
    function dateDifference($date_1 , $date_2 , $differenceFormat = '%m' )
    {
        $datetime1 = date_create($date_1);
        $datetime2 = date_create($date_2);
        
        $interval = date_diff($datetime1, $datetime2);
        
        return $interval->format($differenceFormat);
        
    }
    public function loans_pasivo_mora_report()
    {
        $loans =DB::table('Prestamos')->leftJoin('Padron','Padron.IdPadron','=','Prestamos.IdPadron')
                                        ->where('Prestamos.PresEstPtmo','=','V')
                                        ->where('Prestamos.PresSaldoAct','>',0)
                                        ->where('Padron.PadTipo','=','PASIVO')
                                        // ->where('Padron.PadTipRentAFPSENASIR','=','SENASIR')
                                        ->select('Prestamos.IdPrestamo','Prestamos.PresFechaDesembolso','Prestamos.PresNumero','Prestamos.PresCuotaMensual','Prestamos.PresSaldoAct','Padron.PadTipo','Padron.PadCedulaIdentidad','Padron.PadNombres','Padron.PadNombres2do','Padron.IdPadron','Padron.PadMatricula','Prestamos.SolEntChqCod')
                                      //  ->take(40)
                                        ->get();
    
        global $prestamos;
        $prestamos =[ array('FechaDesembolso','Numero','Cuota','SaldoActual','Tipo','Matricula','CI','Ext','PrimerNombre','SegundoNombre','Paterno','Materno','Frecuencia','Descuento','ciudad','Mese Mora')];

        foreach($loans as $loan)
        {
            $padron = DB::table('Padron')->where('IdPadron','=',$loan->IdPadron)->first();
            $diff=0;
            // $loan->PresNumero = utf8_encode(trim($padron->PresNumero));
            $loan->PadNombres = utf8_encode(trim($padron->PadNombres));
            $loan->PadNombres2do =utf8_encode(trim($padron->PadNombres2do));
            $loan->PadPaterno =utf8_encode(trim($padron->PadPaterno));
            $loan->PadMaterno =utf8_encode(trim($padron->PadMaterno));
            $loan->PadExpCedula =utf8_encode(trim($padron->PadExpCedula));

            $amortizaciones = DB::table('Amortizacion')->where('IdPrestamo','=',$loan->IdPrestamo)->where('AmrSts','<>','X')->get();
            $departamento = DB::table('Departamento')->where('DepCod','=',$loan->SolEntChqCod)->first();
            if($departamento)
            {
                $loan->City =$departamento->DepDsc; 
            }else{
                $loan->City = '';
            }
            if(sizeof($amortizaciones)>0)
            {
                
                $loan->State = 'Recurrente';
                
                if($loan->PresSaldoAct < $loan->PresCuotaMensual)
                {
                    $loan->Discount = $loan->PresSaldoAct;
                }else
                {
                    $loan->Discount = $loan->PresCuotaMensual;
                }
                $amortizacion =   DB::table('Amortizacion')->where('IdPrestamo','=',$loan->IdPrestamo)->where('AmrSts','!=','X')->orderBy('AmrNroPag','desc')->first();

             
                $year = self::dateDifference($amortizacion->AmrFecPag,'2018-08-31','%y');
                $diff  = self::dateDifference($amortizacion->AmrFecPag,'2018-08-31');
                $diff = (int) ($diff+($year*12));
              
                $loan->Diff = $diff;
                $amortizacion = null;


            }



            if($diff>=2)
            {
                $pres_mora = array(
                    $loan->PresFechaDesembolso,
                    $loan->PresNumero,
                    $loan->PresCuotaMensual,
                    $loan->PresSaldoAct,
                    $loan->PadTipo,
                    $loan->PadMatricula,
                    $loan->PadCedulaIdentidad,
                    $loan->PadExpCedula,
                    $loan->PadNombres,
                    $loan->PadNombres2do,
                    $loan->PadPaterno,
                    $loan->PadMaterno,
                    $loan->State,
                    $loan->Discount,
                    $loan->City,
                    $loan->Diff,
                );

                $garantes_id = DB::table('PrestamosLevel1')->where('IdPrestamo','=',$loan->IdPrestamo)->get();
                if(sizeof($garantes_id)>0)
                {
                    foreach($garantes_id as $garante_id)
                    {
                        $padron_gar = DB::table('Padron')->where('IdPadron','=',$garante_id->IdPadronGar)->first();
                        array_push($pres_mora, $padron_gar->PadMatricula,
                                               $padron_gar->PadCedulaIdentidad,
                                               $padron_gar->PadExpCedula,
                                               $padron_gar->PadNombres,
                                               $padron_gar->PadNombres2do,
                                               $padron_gar->PadPaterno,
                                               $padron_gar->PadMaterno,
                                               $padron_gar->PadTipo,
                                               '*'
                                    );
                    }
                }

                array_push($prestamos,$pres_mora);
            }
        }

        Excel::create('prestamos en morar',function($excel)
        {
            global $prestamos;
            
                    $excel->sheet('mora al 31_08_2018',function($sheet) {
                            global $prestamos;
                            $sheet->fromModel($prestamos,null, 'A1', false, false);
                            $sheet->cells('A1:N1', function($cells) {
                            // manipulate the range of cells
                            $cells->setBackground('#058A37');
                            $cells->setFontColor('#ffffff');  
                            $cells->setFontWeight('bold');
                            });
                        });
                  
        })->download('xls');
               
    }
    public function activos_cancelados()
    {
        ini_set ('max_execution_time', 36000); 
        // aumentar el tamaño de memoria permitido de este script: 
        ini_set ('memory_limit', '960M');
        global $rows_exacta,$rows_not_found,$rows_desc_mayor,$rows_desc_menor,$rows_segundo_prestamo,$prestamos_noreg,$rows_gar;
        $path = storage_path('excel/import/MUSERPOL AGOSTO 2018.xls');
        Excel::selectSheetsByIndex(0)->load($path, function($reader) {
            global $rows_exacta,$rows_not_found,$rows_desc_mayor,$rows_desc_menor,$rows_segundo_prestamo,$prestamos_noreg,$rows_gar;
            $rows_exacta = Array();
            $rows_not_found = Array();
            
            array_push($rows_not_found,array('nit','ci','app','apm', 'nom1', 'nom2', 'desc_mes'));
            array_push($rows_exacta,array('nit','ci','app','apm', 'nom1', 'nom2', 'desc_mes','Nro Prestamo','cuota','Saldo'));
            
            // array_push($rows_exacta,array('Prestamos.IdPrestamo',' Prestamos.PresNumero','PresFechaDesembolso','Producto','Prestamos.PresCuotaMensual','Amortizacion.AmrFecPag','Amortizacion.AmrFecTrn','capital','Interes','Interes penal','otros cobros','Amortizacion.AmrNroCpte','Tipo pago','Amortizacion.AmrTotPag','Descuento_comando','Padron.PadMatricula',' Padron.PadCedulaIdentidad','Padron.PadMaterno',' Padron.PadPaterno',' Padron.PadNombres','Padron.PadNombres2do','Padron.Tipo','nit','ci','app','apm', 'nom1', 'nom2', 'desc_mes'));
        
            $result = $reader->select(array('ci','app','apm', 'nom1', 'nom2', 'desc_mes'))
            // ->take(500)
            ->get();
            // $bar = $this->output->createProgressBar(count($result));
         //   $this->info(sizeof($result));
            foreach($result as $row){
                
                $ci = trim($row->ci);
                $descuento = floatval(str_replace(",","",$row->desc_mes));
                // Log::info($row->ci);
                // $padron = DB::table('Padron')->where('PadCedulaIdentidad','=',''.$row->ci)->first();
                $prestamos = DB::table('Prestamos')->join('Padron','Prestamos.IdPadron','=','Padron.IdPadron')
                                                    ->where('Prestamos.PresEstPtmo','=','V')
                                                    ->where('Prestamos.PresSaldoAct','>',0)
                                                    ->where('Padron.PadCedulaIdentidad','=',''.$row->ci)
                                                    ->get();
                if(sizeof($prestamos)>0)
                {
                    // $prestamos = DB::table('Prestamos')->where('Prestamos.PresEstPtmo','=','V')
                    //                                   ->where('Prestamos.PresSaldoAct','>',0)
                    //                                   ->get();
                   
                        foreach($prestamos as $prestamo)
                        {
                            if($row->desc_mes == $prestamo->PresSaldoAct)
                            {
                                array_push($rows_exacta,array($row->nit,$row->ci,$row->app,$row->apm,$row->nom1,$row->nom2,$row->desc_mes,$prestamo->PresNumero,$prestamo->PresCuotaMensual, $prestamo->PresSaldoAct));            
                            }
                        }
                  


                }else{
                    array_push($rows_not_found,array($row->nit,$row->ci,$row->app,$row->apm,$row->nom1,$row->nom2,$row->desc_mes));
                }
                // $bar->advance();
            }
            // $bar->finish();

        });

        Excel::create('prestamos cancelados',function($excel)
        {
            global $rows_exacta,$rows_not_found,$rows_desc_mayor,$rows_desc_menor,$rows_segundo_prestamo,$prestamos_noreg,$rows_gar;
            
                    $excel->sheet('prestamo cancelados',function($sheet) {
                        global $rows_exacta,$rows_not_found,$rows_desc_mayor,$rows_desc_menor,$rows_segundo_prestamo,$prestamos_noreg,$rows_gar;
                            $sheet->fromModel($rows_exacta,null, 'A1', false, false);
                            $sheet->cells('A1:N1', function($cells) {
                            // manipulate the range of cells
                            $cells->setBackground('#058A37');
                            $cells->setFontColor('#ffffff');  
                            $cells->setFontWeight('bold');
                            });
                        });
                    $excel->sheet('no encontrados',function($sheet) {
                        global $rows_exacta,$rows_not_found,$rows_desc_mayor,$rows_desc_menor,$rows_segundo_prestamo,$prestamos_noreg,$rows_gar;
                            $sheet->fromModel($rows_not_found,null, 'A1', false, false);
                            $sheet->cells('A1:N1', function($cells) {
                            // manipulate the range of cells
                            $cells->setBackground('#058A37');
                            $cells->setFontColor('#ffffff');  
                            $cells->setFontWeight('bold');
                            });
                        });
                  
        })->download('xls');
    }

    public function loans_activo_mora_report()
  
    {
        $loans =DB::table('Prestamos')->leftJoin('Padron','Padron.IdPadron','=','Prestamos.IdPadron')
        ->whereIn('Prestamos.IdPrestamo',[10239,10280,10587,10611,10625,10704,10819,10826,11117,11468,11605,11676,11897,11915,12198,12219,12301,12364,12453,12840,12997,13000,13080,13133,13188,13439,13734,13758,13942,13958,14011,14074,14096,14158,14308,14326,14390,14538,14748,14835,14921,14925,15033,15119,15236,15239,15252,20503,25274,17479,20510,16471,16874,15865,15391,20517,28745,15447,17237,17241,27951,28605,30530,26446,17676,16429,20501,29182,20500,20514,28702,22943,28300,17591,20513,28016,16529,16849,20520,30700,29545,16284,28719,28582,16809,27452,28910,28403,29524,20505,24670,27693,15669,21735,28757,30766,20509,23882,28452,29494,19800,30887,28857,27206,15635,16402,18377,26985,29040,21726,21447,30077,29817,17194,21572,28442,28194,23073,21649,23194,20606,28076,21608,28770,22542,22155,30298,21738,34165,32648,32159,32684,33163,33506,32979,33241,34786,33231,34684,32987,34191,33057,33598,34049,34824,33193,34025,34232,33628,34493,34501,32335,33897,34503,34603,33712,34253,32467,32209,31891,31892,35528,36067,38099,37486,38039,38418,36904,37734,36042,34958,36146,36996,37638,38676,38741,38585,37924,35424,37533,37022,37874,35603,35985,37386,38129,36710,37798,38529,37983,38029,38209,37096,37288,37343,35653,36396,36764,36822,36954,37048,36487,35003,35618,35757,36264,36862,37361,35931,34942,36011,41219,40191,41263,39897,39428,41259,41548,41940,41692,41334,41690,41911,41099,39149,41654,40023,41465,41494,42290,40317,39134,39963,40070,39462,39775,39128,39649,39208,38914,39353,39405,39750,40224,39482,39727,39782,39189,44539,47932,48333,43696,46737,48220,47013,47927,48217,47256,47368,48055,48385,48535,45503,45994,47668,47757,47773,48221,48373,44801,45362,45870,45963,47460,47590,47796,44974,44278,46125,46127,46890,47060,47147,43672,43917,45564,46191,44882,43228,44266,42804,44257,44793,45230,45574,45642,43463,44733,44820,43375,43452,44212,44258,43240,44000,42941,43218,50749,50753,50755,50767,50769,50786,50793,50815,50844,50874,50883,50931,50935,50972,50977,51028,51032,51087,51139,51149,51212,51274,51324,49179,49486,50853,48608,49190,49266,49457,50505,49722,49733,49763,50031,50090,50624,49092,49302,48646,48800,48932,48708,49219])
        // ->where('Padron.PadTipRentAFPSENASIR','=','SENASIR')
        ->select('Prestamos.IdPrestamo','Prestamos.PresFechaDesembolso','Prestamos.PresNumero','Prestamos.PresCuotaMensual','Prestamos.PresSaldoAct','Padron.PadTipo','Padron.PadCedulaIdentidad','Padron.PadNombres','Padron.PadNombres2do','Padron.IdPadron','Padron.PadMatricula','Prestamos.SolEntChqCod')
      //  ->take(40)
        ->get();

        global $prestamos;
        $prestamos =[ array('FechaDesembolso','Numero','Cuota','SaldoActual','Tipo','Matricula','CI','Ext','PrimerNombre','SegundoNombre','Paterno','Materno','Frecuencia','Descuento','ciudad','Mese Mora')];

        foreach($loans as $loan)
        {
        $padron = DB::table('Padron')->where('IdPadron','=',$loan->IdPadron)->first();
        $diff=0;
        // $loan->PresNumero = utf8_encode(trim($padron->PresNumero));
        $loan->PadNombres = utf8_encode(trim($padron->PadNombres));
        $loan->PadNombres2do =utf8_encode(trim($padron->PadNombres2do));
        $loan->PadPaterno =utf8_encode(trim($padron->PadPaterno));
        $loan->PadMaterno =utf8_encode(trim($padron->PadMaterno));
        $loan->PadExpCedula =utf8_encode(trim($padron->PadExpCedula));

        $amortizaciones = DB::table('Amortizacion')->where('IdPrestamo','=',$loan->IdPrestamo)->where('AmrSts','<>','X')->get();
        $departamento = DB::table('Departamento')->where('DepCod','=',$loan->SolEntChqCod)->first();
        if($departamento)
        {
        $loan->City =$departamento->DepDsc; 
        }else{
        $loan->City = '';
        }
        if(sizeof($amortizaciones)>0)
        {

        $loan->State = 'Recurrente';

        if($loan->PresSaldoAct < $loan->PresCuotaMensual)
        {
        $loan->Discount = $loan->PresSaldoAct;
        }else
        {
        $loan->Discount = $loan->PresCuotaMensual;
        }
        $amortizacion =   DB::table('Amortizacion')->where('IdPrestamo','=',$loan->IdPrestamo)->where('AmrSts','!=','X')->orderBy('AmrNroPag','desc')->first();


        $year = self::dateDifference($amortizacion->AmrFecPag,'2018-08-31','%y');
        $diff  = self::dateDifference($amortizacion->AmrFecPag,'2018-08-31');
        $diff = (int) ($diff+($year*12));

        $loan->Diff = $diff;
        $amortizacion = null;


        }



        if($diff>=2)
        {
        $pres_mora = array(
        $loan->PresFechaDesembolso,
        $loan->PresNumero,
        $loan->PresCuotaMensual,
        $loan->PresSaldoAct,
        $loan->PadTipo,
        $loan->PadMatricula,
        $loan->PadCedulaIdentidad,
        $loan->PadExpCedula,
        $loan->PadNombres,
        $loan->PadNombres2do,
        $loan->PadPaterno,
        $loan->PadMaterno,
        $loan->State,
        $loan->Discount,
        $loan->City,
        $loan->Diff,
        );

        $garantes_id = DB::table('PrestamosLevel1')->where('IdPrestamo','=',$loan->IdPrestamo)->get();
        if(sizeof($garantes_id)>0)
        {
        foreach($garantes_id as $garante_id)
        {
        $padron_gar = DB::table('Padron')->where('IdPadron','=',$garante_id->IdPadronGar)->first();
        array_push($pres_mora, $padron_gar->PadMatricula,
                    $padron_gar->PadCedulaIdentidad,
                    $padron_gar->PadExpCedula,
                    $padron_gar->PadNombres,
                    $padron_gar->PadNombres2do,
                    $padron_gar->PadPaterno,
                    $padron_gar->PadMaterno,
                    $padron_gar->PadTipo,
                    '*'
            );
        }
        }

        array_push($prestamos,$pres_mora);
        }
        }

        Excel::create('prestamos en morar',function($excel)
        {
        global $prestamos;

        $excel->sheet('mora al 31_08_2018',function($sheet) {
        global $prestamos;
        $sheet->fromModel($prestamos,null, 'A1', false, false);
        $sheet->cells('A1:N1', function($cells) {
        // manipulate the range of cells
        $cells->setBackground('#058A37');
        $cells->setFontColor('#ffffff');  
        $cells->setFontWeight('bold');
        });
        });

        })->download('xls');
    }
}
