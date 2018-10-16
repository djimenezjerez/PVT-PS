<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use DB;
use Log;
class PrestamosDoblesExactos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prestamos:PrestamosDoblesExactos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'paso 4:para subida al sismu hdp';

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
        $path = storage_path('excel/export/3_prestamos con descuentos exactos.xls');
        Excel::selectSheetsByIndex(1)->load($path, function($reader) {
            global $rows_exacta,$rows_not_found,$rows_desc_mayor,$rows_desc_menor,$rows_segundo_prestamo,$prestamos_noreg,$rows_gar;
            $rows_exacta = Array();
            $rows_not_found = Array();
            
            array_push($rows_not_found,array('nit','ci','app','apm', 'nom1', 'nom2', 'desc_mes'));
            array_push($rows_exacta,array('nit','ci','app','apm', 'nom1', 'nom2', 'desc_mes','Nro Prestamo','cuota','Saldo'));
            
            // array_push($rows_exacta,array('Prestamos.IdPrestamo',' Prestamos.PresNumero','PresFechaDesembolso','Producto','Prestamos.PresCuotaMensual','Amortizacion.AmrFecPag','Amortizacion.AmrFecTrn','capital','Interes','Interes penal','otros cobros','Amortizacion.AmrNroCpte','Tipo pago','Amortizacion.AmrTotPag','Descuento_comando','Padron.PadMatricula',' Padron.PadCedulaIdentidad','Padron.PadMaterno',' Padron.PadPaterno',' Padron.PadNombres','Padron.PadNombres2do','Padron.Tipo','nit','ci','app','apm', 'nom1', 'nom2', 'desc_mes'));
        
            $result = $reader->select(array('ci','app','apm', 'nom1', 'nom2', 'desc_mes'))
                             ->get();
            $bar = $this->output->createProgressBar(count($result));
            $this->info(sizeof($result));
            foreach($result as $row){
                
                $ci = trim($row->ci);
                $prestamos = DB::table('Prestamos')->join('Padron','Prestamos.IdPadron','=','Padron.IdPadron')
                                                ->where('Prestamos.PresEstPtmo','=','V')
                                                ->where('Prestamos.PresSaldoAct','>',0)
                                                ->where('Padron.PadCedulaIdentidad','=',''.$row->ci)
                                                ->select('Prestamos.IdPrestamo','Prestamos.PresNumero','Prestamos.PresSaldoAct','Prestamos.PresCuotaMensual')
                                                ->groupBy('Prestamos.IdPrestamo','Prestamos.PresNumero','Prestamos.PresSaldoAct','Prestamos.PresCuotaMensual')
                                                ->get();
                if(sizeof($prestamos)>0)
                {
                 
                    $amount=0;
                    foreach($prestamos as $prestamo)
                    {
                        $amount+=$prestamo->PresCuotaMensual;
                    }

                    if($amount == $row->desc_mes)
                    {
                        $i=0;
                        foreach($prestamos as $prestamo)
                        {    
                            $i++;    
                            array_push($rows_exacta,array($row->nit,$row->ci,$row->app,$row->apm,$row->nom1,$row->nom2,$row->desc_mes,$prestamo->PresNumero,$prestamo->PresCuotaMensual, $prestamo->PresSaldoAct));               
                        }
                        Log::info($i);
                    }else{
                        array_push($rows_not_found,array($row->nit,$row->ci,$row->app,$row->apm,$row->nom1,$row->nom2,$row->desc_mes));
                    }   

                    
                }else{
                    array_push($rows_not_found,array($row->nit,$row->ci,$row->app,$row->apm,$row->nom1,$row->nom2,$row->desc_mes));
                }
                
                $bar->advance();
            }
            $bar->finish();

        });

        Excel::create('4_prestamos dobles exactos',function($excel)
        {
            global $rows_exacta,$rows_not_found,$rows_desc_mayor,$rows_desc_menor,$rows_segundo_prestamo,$prestamos_noreg,$rows_gar;
            
                    $excel->sheet('prestamos dobles exactos',function($sheet) {
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
