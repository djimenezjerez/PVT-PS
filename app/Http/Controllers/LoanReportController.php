<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use DB;
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
}
