<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use DB;
use Log;
class NuevosPrestamos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prestamos:NuevosPrestamos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'paso 2';

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
        //
        global $rows_exacta,$rows_not_found,$rows_desc_mayor,$rows_desc_menor,$rows_segundo_prestamo,$prestamos_noreg,$rows_gar;
        $path = storage_path('excel/export/prestamos cancelados.xls');
        Excel::selectSheetsByIndex(1)->load($path, function($reader) {
            global $rows_exacta,$rows_not_found,$rows_desc_mayor,$rows_desc_menor,$rows_segundo_prestamo,$prestamos_noreg,$rows_gar;
            $rows_exacta = Array();
            $rows_not_found = Array();
            
            array_push($rows_not_found,array('nit','ci','app','apm', 'nom1', 'nom2', 'desc_mes'));
            array_push($rows_exacta,array('nit','ci','app','apm', 'nom1', 'nom2', 'desc_mes','Nro Prestamo','cuota','Primera cuota','Saldo'));
            
            // array_push($rows_exacta,array('Prestamos.IdPrestamo',' Prestamos.PresNumero','PresFechaDesembolso','Producto','Prestamos.PresCuotaMensual','Amortizacion.AmrFecPag','Amortizacion.AmrFecTrn','capital','Interes','Interes penal','otros cobros','Amortizacion.AmrNroCpte','Tipo pago','Amortizacion.AmrTotPag','Descuento_comando','Padron.PadMatricula',' Padron.PadCedulaIdentidad','Padron.PadMaterno',' Padron.PadPaterno',' Padron.PadNombres','Padron.PadNombres2do','Padron.Tipo','nit','ci','app','apm', 'nom1', 'nom2', 'desc_mes'));
        
            $result = $reader->select(array('ci','app','apm', 'nom1', 'nom2', 'desc_mes'))
                             //->take(100)
                             ->get();
            
            


            $bar = $this->output->createProgressBar(count($result));
            $this->info(sizeof($result));
            foreach($result as $row){
                
                $ci = trim($row->ci);
                $prestamo = DB::table('Prestamos') ->join('Padron','Prestamos.IdPadron','=','Padron.IdPadron')
                                                    ->join('PlanPagosPlan','PlanPagosPlan.IdPrestamo','=','Prestamos.IdPrestamo')
                                                    ->where('Prestamos.PresEstPtmo','=','V')
                                                    ->where('Prestamos.PresSaldoAnt','=',0)
                                                    ->where('Padron.PadCedulaIdentidad','=',''.$row->ci)
                                                    ->whereRaw('day(PlanPagosPlan.PlanFechaPago) = 31 and MONTH(PlanPagosPlan.PlanFechaPago)=8 and YEAR(PlanPagosPlan.PlanFechaPago) = 2018 and PlanPagosPlan.IdPlanNroCouta = 1')
                                                    ->select('Prestamos.IdPrestamo','Prestamos.PresNumero','Prestamos.PresSaldoAct','Prestamos.PresCuotaMensual','PlanPagosPlan.PlanCuotaMensual')
                                                    ->groupBy('Prestamos.IdPrestamo','Prestamos.PresNumero','Prestamos.PresSaldoAct','Prestamos.PresCuotaMensual','PlanPagosPlan.PlanCuotaMensual')
                                                    ->first();
                if($prestamo)
                {
                    // $this->info(json_encode($prestamo));
                    array_push($rows_exacta,array($row->nit,$row->ci,$row->app,$row->apm,$row->nom1,$row->nom2,$row->desc_mes,$prestamo->PresNumero,$prestamo->PresCuotaMensual,$prestamo->PlanCuotaMensual, $prestamo->PresSaldoAct));            
                        
                }else{
                    array_push($rows_not_found,array($row->nit,$row->ci,$row->app,$row->apm,$row->nom1,$row->nom2,$row->desc_mes));
                }
                $bar->advance();
            }
            $bar->finish();

        });

        Excel::create('prestamos 2_nuevos',function($excel)
        {
            global $rows_exacta,$rows_not_found,$rows_desc_mayor,$rows_desc_menor,$rows_segundo_prestamo,$prestamos_noreg,$rows_gar;
            
                    $excel->sheet('prestamo nuevos',function($sheet) {
                        global $rows_exacta,$rows_not_found,$rows_desc_mayor,$rows_desc_menor,$rows_segundo_prestamo,$prestamos_noreg,$rows_gar;
                            $sheet->fromModel($rows_exacta,null, 'A1', false, false);
                            $sheet->cells('A1:N1', function($cells) {
                            // manipulate the range of cells
                            $cells->setBackground('#058A37');
                            $cells->setFontColor('#ffffff');  
                            $cells->setFontWeight('bold');
                            });
                        });
                    $excel->sheet('comando restante',function($sheet) {
                        global $rows_exacta,$rows_not_found,$rows_desc_mayor,$rows_desc_menor,$rows_segundo_prestamo,$prestamos_noreg,$rows_gar;
                            $sheet->fromModel($rows_not_found,null, 'A1', false, false);
                            $sheet->cells('A1:N1', function($cells) {
                            // manipulate the range of cells
                            $cells->setBackground('#058A37');
                            $cells->setFontColor('#ffffff');  
                            $cells->setFontWeight('bold');
                            });
                        });
                  
        })->store('xls', storage_path('excel/export'));
        $this->info('Finished');

    }
}
