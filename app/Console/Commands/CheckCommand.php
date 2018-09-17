<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use DB;
use Log;
class CheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prestamos:CheckCommand';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        global $rows_exacta,$rows_not_found,$rows_desc_mayor,$rows_desc_menor,$rows_segundo_prestamo,$prestamos_noreg,$rows_gar;
        $path = storage_path('excel/import/comando agosto oficial.xls');
        Excel::selectSheetsByIndex(0)->load($path, function($reader) {
            global $rows_exacta,$rows_not_found,$rows_desc_mayor,$rows_desc_menor,$rows_segundo_prestamo,$prestamos_noreg,$rows_gar;
            $rows_exacta = Array();
            $rows_not_found = Array();

            array_push($rows_not_found,array('ci','app','apm', 'nom1', 'nom2', 'desc_mes'));
            array_push($rows_exacta,array('ci','app','apm', 'nom1', 'nom2', 'desc_mes'));
            
            $result = $reader->select(array('ci','app','apm', 'nom1', 'nom2', 'desc_mes'))
                             ->get();
            $suma = 0;
            $bar = $this->output->createProgressBar(count($result));
            $this->info(sizeof($result));
            foreach($result as $row){
                $suma += $row->desc_mes;
                $ci = trim($row->ci);
                $descuento = floatval($row->desc_mes);
                $padron = DB::table('Padron')->where('PadCedulaIdentidad','=',''.$row->ci)->first();
                
                if($padron)
                {
                    array_push($rows_exacta,array($row->ci,$row->app,$row->apm,$row->nom1,$row->nom2,$row->desc_mes));             
                }else{
                    array_push($rows_not_found,array($row->ci,$row->app,$row->apm,$row->nom1,$row->nom2,$row->desc_mes));
                }
                $bar->advance();
            }
            $bar->finish();
            $this->info(" Descuentos Total:".$suma);
        });

        
        Excel::create('lista_comandos',function($excel)
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
                  
        })->store('xls', storage_path('excel/export'));
        $this->info('Finished');
    }
}
