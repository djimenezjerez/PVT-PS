<?php

namespace App\Http\Controllers;

use App\Loan;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use DB;
use Log;
use Datetime;
use Carbon\Carbon;
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
                        ->where('Prestamos.PresEstPtmo','!=','X')
                        // ->where('Prestamos.PresSaldoAct','>',0)
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
                        ->where('Prestamos.PresEstPtmo','!=','X')
                        // ->where('Prestamos.PresSaldoAct','>',0)
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
       return json_encode($loans);
    }
    public function loans_senasir()
    {
    
        $loans =DB::table('Prestamos')->leftJoin('Padron','Padron.IdPadron','=','Prestamos.IdPadron')
                                        ->where('Prestamos.PresEstPtmo','=','V')
                                        ->where('Prestamos.PresSaldoAct','>',0)
                                        ->where('Padron.PadTipo','=','PASIVO')
                                        ->where('Padron.PadTipRentAFPSENASIR','=','SENASIR')
                                        ->select('Prestamos.IdPrestamo','Prestamos.PresFechaDesembolso','Prestamos.PresNumero','Prestamos.PresCuotaMensual','Prestamos.PresSaldoAct','Padron.PadTipo','Padron.PadCedulaIdentidad','Padron.PadNombres','Padron.PadNombres2do','Padron.IdPadron','Padron.PadMatricula','Prestamos.SolEntChqCod')
                                    //  ->take(40)
                                        ->get();
    
        $prestamos = [];

        foreach($loans as $loan)
        {
            $padron = DB::table('Padron')->where('IdPadron','=',$loan->IdPadron)->first();
           
            // $loan->PresNumero = utf8_encode(trim($padron->PresNumero));
            $loan->PadNombres = utf8_encode(trim($padron->PadNombres));
            $loan->PadNombres2do =utf8_encode(trim($padron->PadNombres2do));
            $loan->PadPaterno =utf8_encode(trim($padron->PadPaterno));
            $loan->PadMaterno =utf8_encode(trim($padron->PadMaterno));

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

            array_push($prestamos,$loan);
        }
   
       return json_encode($prestamos);
    }
    public function loans_in_arrears()
    {
        
        $loans =DB::table('Prestamos')->leftJoin('Padron','Padron.IdPadron','=','Prestamos.IdPadron')
                                        ->where('Prestamos.PresEstPtmo','=','V')
                                        ->where('Prestamos.PresSaldoAct','>',0)
                                        ->where('Padron.PadTipo','=','PASIVO')
                                        // ->where('Prestamos.IdPrestamo','=',53251)
                                        // ->where('Padron.PadTipRentAFPSENASIR','=','SENASIR')
                                        ->select('Prestamos.IdPrestamo','Prestamos.PresFechaDesembolso','Prestamos.PresNumero','Prestamos.PresCuotaMensual','Prestamos.PresSaldoAct','Padron.PadTipo','Padron.PadCedulaIdentidad','Padron.PadNombres','Padron.PadNombres2do','Padron.IdPadron','Padron.PadMatricula','Prestamos.SolEntChqCod','Prestamos.prestasaint')
                                    //  ->take(40)
                                        ->get();
        $prestamos = [];

        foreach($loans as $loan)
        {
            $padron = DB::table('Padron')->where('IdPadron','=',$loan->IdPadron)->first();
            $diff=0;
            // $loan->PresNumero = utf8_encode(trim($padron->PresNumero));
            $loan->PadNombres = utf8_encode(trim($padron->PadNombres));
            $loan->PadNombres2do =utf8_encode(trim($padron->PadNombres2do));
            $loan->PadPaterno =utf8_encode(trim($padron->PadPaterno));
            $loan->PadMaterno =utf8_encode(trim($padron->PadMaterno));

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
                Log::info('diff:'.$diff);
                array_push($prestamos,$loan);
            }
        }
        return json_encode($prestamos);
    }
  
    function dateDifference($date_1 , $date_2 , $differenceFormat = '%m' )
    {
        $datetime1 = date_create($date_1);
        $datetime2 = date_create($date_2);
        
        $interval = date_diff($datetime1, $datetime2);
        
        return $interval->format($differenceFormat);
        
    }

    public function loans_command()
    {
        $loans = DB::table('Prestamos')
                        ->join('Padron','Padron.IdPadron','=','Prestamos.IdPadron')
                        ->where('Prestamos.PresEstPtmo','=','V')
                        ->where('Prestamos.PresSaldoAct','>',0)
                        ->where('Padron.PadTipo','=','ACTIVO')
                        ->whereNotIn('Padron.PadTipRentAFPSENASIR',["AFP'S FUTURO","AFP'S PREVISION","AFPS' PREVISION","LA VITALICIA","SENASIR"])
                        ->select('Prestamos.IdPrestamo','Prestamos.PresFechaDesembolso','Prestamos.PresNumero','Prestamos.PresCuotaMensual','Prestamos.PresSaldoAct','Padron.PadTipo','Padron.PadCedulaIdentidad','Padron.PadNombres','Padron.PadNombres2do','Padron.IdPadron','Padron.PadMatricula','Prestamos.SolEntChqCod')
                       // ->take(3000)
                        ->get();
        global $prestamos,$prestamos_sin_plan;
        $prestamos_sin_plan = [];
        $prestamos =[];
        foreach($loans  as $loan)
        {   
            $padron = DB::table('Padron')->where('IdPadron','=',$loan->IdPadron)->first();
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
                $plan_de_pago = DB::table('PlanPagosPlan')
                                    ->where('IdPrestamo','=',$loan->IdPrestamo)
                                    ->where('IdPlanNroCouta','=',1)
                                    ->whereraw("PlanFechaPago <  cast('2018-10-31' as datetime)")
                                    ->first();
               
                $loan->Discount = $plan_de_pago->PlanCuotaMensual;

            }

            array_push($prestamos,$loan);

        }

        return json_encode($prestamos);
    }
    public function export_loans(){
        global $states_loan,$gestion;
        $gestion= 'PTMO-19';  
        // select count(dbo.Prestamos.PresEstPtmo) as cantidad ,dbo.EstadoPrestamo.PresEstDsc as nombre,dbo.EstadoPrestamo.PresEstPtmo as id from dbo.Prestamos
        // join dbo.EstadoPrestamo on Prestamos.PresEstPtmo = EstadoPrestamo.PresEstPtmo
        // where dbo.Prestamos.PresNumero like 'PTMO-1800%'
        // GROUP by dbo.Prestamos.PresEstPtmo,EstadoPrestamo.PresEstDsc,dbo.EstadoPrestamo.PresEstPtmo;

        $states_loan = DB::table('Prestamos')
                                ->join('EstadoPrestamo','EstadoPrestamo.PresEstPtmo','=','EstadoPrestamo.PresEstPtmo') 
                                ->where('Prestamos.PresNumero','like',$gestion.'%')
                                ->select('Prestamos.PresEstPtmo')
                                // ->select('EstadoPrestamo.PresEstDsc as nombre','dbo.EstadoPrestamo.PresEstPtmo as id')
                                // ->select(DB::raw("count(dbo.Prestamos.PresEstPtmo) as cantidad ,dbo.EstadoPrestamo.PresEstDsc as nombre,dbo.EstadoPrestamo.PresEstPtmo as id"))
                                ->groupBy('Prestamos.PresEstPtmo')
                                ->get();
        
        Excel::create('Prestamos 2019', function($excel) {
            global $loans_details,$states_loan,$gestion;
        foreach($states_loan as $state){
            // $prestamo DB::table('Prestamo')->

                global $estado ;
                $estado = DB::table('EstadoPrestamo')
                            ->where('PresEstPtmo',$state->PresEstPtmo)
                            ->first();
                $prestamos = DB::table('Prestamos')
                            // ->join('Padron','Padron.IdPadron','=','Prestamos.IdPadron')
                            ->where('Prestamos.PresNumero','like',$gestion.'%')
                            ->where('Prestamos.PresEstPtmo','=',$state->PresEstPtmo)
                            ->select('Prestamos.PresSaldoAct','Prestamos.PresPresupNroCpte','Prestamos.PresFechaPrestamo','Prestamos.PresFechaDesembolso','Prestamos.PresMntDesembolso','Prestamos.PresEstPtmo','Prestamos.PresAmp','Prestamos.IdPadron'
                                    //  'Padron.PadCedulaIdentidad','Padron.PadExpCedula','Padron.PadMaterno'
                                    )
                            ->get();
                $loans_details = array(array('1er Nombre','2do Nombre','Ap. Paterno','Ap. Materno','CI','Exp','Saldo Actual','Nro Comprobante','Fecha de Prestamo','Fecha de Desembolso','Monto Desembolsado','Estado','Ampliacion'));
                foreach($prestamos as $prestamo){
                    $padron = DB::table('Padron')->where('IdPadron',$prestamo->IdPadron)->first();
                    $prestamo->PadTipo = utf8_encode(trim($padron->PadTipo));
                    $prestamo->PadNombres = utf8_encode(trim($padron->PadNombres));
                    $prestamo->PadNombres2do =utf8_encode(trim($padron->PadNombres2do));
                    $prestamo->PadPaterno =utf8_encode(trim($padron->PadPaterno));
                    $prestamo->PadMaterno =utf8_encode(trim($padron->PadMaterno));
                    $prestamo->PadCedulaIdentidad =utf8_encode(trim($padron->PadCedulaIdentidad));
                    $prestamo->PadExpCedula =utf8_encode(trim($padron->PadExpCedula));
                    // $item->PadMatricula =utf8_encode(trim($padron->PadMatricula));
                    // $item->PadMatriculaTit =utf8_encode(trim($padron->PadMatriculaTit));
                    // array_push($loans_details,array('PadTipo'=> $prestamo->PadTipo,'PadNombres'=>$prestamo->PadNombres,'PadNombres2do'=>$prestamo->PadNombres2do,'PadPaterno'=>$prestamo->PadPaterno,'PadMaterno'=>$prestamo->PadMaterno,'PadCedulaIdentidad'=>$prestamo->PadCedulaIdentidad,
                    //                                 'PresSaldoAct' => $prestamo->PresSaldoAct, 'PresPresupNroCpte' => $prestamo->PresPresupNroCpte,'PresMntDesembolso'=>$prestamo->PresMntDesembolso,'PresEstPtmo'=>$prestamo->PresEstPtmo,'PresAmp'=>$prestamo->PresAmp
                    //                                 ));
                    array_push($loans_details,array($prestamo->PadNombres,$prestamo->PadNombres2do,$prestamo->PadPaterno,$prestamo->PadMaterno,$prestamo->PadCedulaIdentidad,$prestamo->PadExpCedula,
                                                    $prestamo->PresSaldoAct,$prestamo->PresPresupNroCpte, $prestamo->PresFechaPrestamo, $prestamo->PresFechaDesembolso,$prestamo->PresMntDesembolso,$prestamo->PresEstPtmo,$prestamo->PresAmp
                                                    ));
                    

                }

                $excel->sheet('Prestamos '.$estado->PresEstDsc, function($sheet) {
                    global $loans_details;
                    $sheet->fromArray($loans_details);
            
                });
                
            }
        })->download('xls');
        // return json_encode($loans);
    }
    public function certificate_info(){
        $id_prestamo = request('id_prestamo')??'';
        // return $id_prestamo;

        $loan = DB::table('Prestamos')->where('IdPrestamo',$id_prestamo)
                                    ->join('Producto','Producto.PrdCod','=','Prestamos.PrdCod')
                                    ->select('Prestamos.IdPadron','Prestamos.PresNumero','Prestamos.PresFechaDesembolso','Producto.PrdDsc as producto')
                                    ->first();
        $literal_date = Self::getDateFormat($loan->PresFechaDesembolso,'large');
        $padron = DB::table('Padron')->where('IdPadron',$loan->IdPadron)->select('PadCedulaIdentidad')->first();
        $affiliate = DB::connection('virtual_platform')->table('affiliates')
                                                        ->join('degrees','degrees.id','=','affiliates.degree_id')    
                                                        ->leftJoin('cities','cities.id','=','affiliates.city_identity_card_id')
                                                        ->where('affiliates.identity_card',trim($padron->PadCedulaIdentidad))
                                                        ->select('degrees.shortened as grado','affiliates.first_name','affiliates.second_name','affiliates.last_name','affiliates.mothers_last_name','affiliates.identity_card','cities.first_shortened as ext')
                                                        ->first();
        $amortization = DB::table('Amortizacion')->where('IdPrestamo',$id_prestamo)
                                                 ->where('AmrSts','!=','X')
                                                 ->select('AmrCap','AmrInt','AmrIntPen','AmrTotPag','AmrSldAnt')
                                                 ->orderBy('AmrNroPag', 'DESC')->first(); 
        $data = array(
                'prestamo'=> $loan,
                'padron'=> $padron,
                'affiliate'=> $affiliate,
                'amortizacion'=> $amortization,
                'literal_date' => $literal_date,
                'literal_money' => Self::convertir($amortization->AmrSldAnt),
                'literal_today' => Self::getDateFormat(Carbon::now(),'large')
        );
        return json_encode($data);

    }
    public static function getDateFormat($date, $size='short')
    {
        setlocale(LC_TIME, 'es_ES.utf8');
        if ($date) {
            if ($size == 'short') {
                // return 05 MAY. 1983 // change %d-> %e for 5 MAY. 1983
                return Carbon::parse($date)->formatLocalized('%d %b %Y'); //
            }elseif ($size == 'large') {
                return Carbon::parse($date)->formatLocalized('%d de %B de %Y'); //
            }
        }
        return 'sin fecha';
    }
    public static function convertir($number, $moneda = '', $centimos = '')
    {
        $converted = '';
        $decimales = '';
        if (($number < 0) || ($number > 999999999)) {
            return 'No es posible convertir el numero a letras';
        }
        $div_decimales = explode('.',$number);
        if(count($div_decimales) > 1){
            $number = $div_decimales[0];
            $decNumberStr = (string) $div_decimales[1];
            if(strlen($decNumberStr) == 2){
                $decNumberStrFill = str_pad($decNumberStr, 9, '0', STR_PAD_LEFT);
                $decCientos = substr($decNumberStrFill, 6);
                $decimales = self::convertGroup($decCientos);
            }
        }
        $numberStr = (string) $number;
        $numberStrFill = str_pad($numberStr, 9, '0', STR_PAD_LEFT);
        $millones = substr($numberStrFill, 0, 3);
        $miles = substr($numberStrFill, 3, 3);
        $cientos = substr($numberStrFill, 6);
        if (intval($millones) > 0) {
            if ($millones == '001') {
                $converted .= 'UN MILLON ';
            } else if (intval($millones) > 0) {
                $converted .= sprintf('%sMILLONES ', self::convertGroup($millones));
            }
        }
        if (intval($miles) > 0) {
            if ($miles == '001') {
                $converted .= 'UN MIL ';
            } else if (intval($miles) > 0) {
                $converted .= sprintf('%sMIL ', self::convertGroup($miles));
            }
        }
        if (intval($cientos) > 0) {
            if ($cientos == '001') {
                $converted .= 'UN ';
            } else if (intval($cientos) > 0) {
                $converted .= sprintf('%s ', self::convertGroup($cientos));
            }
        }
        if(empty($decimales)){
            // $valor_convertido = $converted . strtoupper($moneda);
            $valor_convertido = $converted . '00/100';
        } else {
            $valor_convertido = $converted . strtoupper($moneda)  . ($div_decimales[1]) . '/100';
            // $valor_convertido = $converted . strtoupper($moneda) . ' CON ' . $decimales . ' ' . strtoupper($centimos);
        }
        return $valor_convertido;
    }
    private static function convertGroup($n)
    {
        $output = '';
        if ($n == '100') {
            $output = "CIEN ";
        } else if ($n[0] !== '0') {
            $output = self::$CENTENAS[$n[0] - 1];
        }
        $k = intval(substr($n,1));
        if ($k <= 20) {
            $output .= self::$UNIDADES[$k];
        } else {
            if(($k > 30) && ($n[2] !== '0')) {
                $output .= sprintf('%sY %s', self::$DECENAS[intval($n[1]) - 2], self::$UNIDADES[intval($n[2])]);
            } else {
                $output .= sprintf('%s%s', self::$DECENAS[intval($n[1]) - 2], self::$UNIDADES[intval($n[2])]);
            }
        }
        return $output;
    }

    private static $UNIDADES = [
        '',
        'UN ',
        'DOS ',
        'TRES ',
        'CUATRO ',
        'CINCO ',
        'SEIS ',
        'SIETE ',
        'OCHO ',
        'NUEVE ',
        'DIEZ ',
        'ONCE ',
        'DOCE ',
        'TRECE ',
        'CATORCE ',
        'QUINCE ',
        'DIECISEIS ',
        'DIECISIETE ',
        'DIECIOCHO ',
        'DIECINUEVE ',
        'VEINTE '
    ];
    private static $DECENAS = [
        'VEINTI',
        'TREINTA ',
        'CUARENTA ',
        'CINCUENTA ',
        'SESENTA ',
        'SETENTA ',
        'OCHENTA ',
        'NOVENTA ',
        'CIEN '
    ];
    private static $CENTENAS = [
        'CIENTO ',
        'DOSCIENTOS ',
        'TRESCIENTOS ',
        'CUATROCIENTOS ',
        'QUINIENTOS ',
        'SEISCIENTOS ',
        'SETECIENTOS ',
        'OCHOCIENTOS ',
        'NOVECIENTOS '
    ];
}
