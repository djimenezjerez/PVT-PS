<?php

namespace App\Http\Controllers;

use App\Loan;
use Illuminate\Http\Request;
use DB;
use Log;
use Datetime;
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
                                        ->select('Prestamos.IdPrestamo','Prestamos.PresFechaDesembolso','Prestamos.PresNumero','Prestamos.PresCuotaMensual','Prestamos.PresSaldoAct','Padron.PadTipo','Padron.PadCedulaIdentidad','Padron.PadNombres','Padron.PadNombres2do','Padron.IdPadron','Padron.PadMatricula','Prestamos.SolEntChqCod')
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
}
