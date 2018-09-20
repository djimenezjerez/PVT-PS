<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use DB;
use Log;
class PlanillaComando extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prestamos:PlanillaComando';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generador de la planilla de altas XD';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // no mandar a ["AFP'S FUTURO","AFP'S PREVISION","AFPS' PREVISION","LA VITALICIA","SENASIR"]          
       $loans = DB::table('Prestamos')
                        ->join('Padron','Padron.IdPadron','=','Prestamos.IdPadron')
                        ->where('Prestamos.PresEstPtmo','=','V')
                        ->where('Prestamos.PresSaldoAct','>',0)
                        ->where('Padron.PadTipo','=','ACTIVO')
                        ->whereNotIn('Padron.PadTipRentAFPSENASIR',["AFP'S FUTURO","AFP'S PREVISION","AFPS' PREVISION","LA VITALICIA","SENASIR"])
                        ->select('Prestamos.IdPrestamo','Prestamos.PresFechaDesembolso','Prestamos.PresNumero','Prestamos.PresCuotaMensual','Prestamos.PresSaldoAct','Padron.PadTipo','Padron.PadCedulaIdentidad','Padron.PadNombres','Padron.PadNombres2do','Padron.IdPadron','Padron.PadMatricula','Prestamos.SolEntChqCod')
                       // ->take(3000)
                        ->get();
        $this->info(sizeof($loans));

        global $prestamos,$prestamos_sin_plan;
        $prestamos_sin_plan = [];
        $prestamos =[ array('FechaDesembolso','Numero','Cuota','SaldoActual','Tipo','MatriculaTitular','MatriculaDerechohabiente','CI','Extension','PrimerNombre','SegundoNombre','Paterno','Materno','Frecuencia','Descuento','ciudad')];
        $bar = $this->output->createProgressBar(count($loans));
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
                $plan_de_pago = DB::table('PlanPagosPlan')->where('IdPrestamo','=',$loan->IdPrestamo)->where('IdPlanNroCouta','=',1)->first();
               
                $loan->Discount = $plan_de_pago->PlanCuotaMensual;

            }

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

            $bar->advance();
        }
        $bar->finish();

        Excel::create('prestamos sin plan',function($excel)
        {
            
            global $prestamos,$prestamos_sin_plan;
            
                    $excel->sheet('prestamo ',function($sheet) {
                        global $prestamos,$prestamos_sin_plan;
                            $sheet->fromModel($prestamos,null, 'A1', false, false);
                            $sheet->cells('A1:N1', function($cells) {
                            // manipulate the range of cells
                            $cells->setBackground('#058A37');
                            $cells->setFontColor('#ffffff');  
                            $cells->setFontWeight('bold');
                            });
                        });
                
                  
        })->store('xls', storage_path('excel/export'));
        $this->info('Finished');


        $this->info('Termino XD');

    }
}
