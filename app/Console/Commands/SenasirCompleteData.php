<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use DB;

class SenasirCompleteData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'keyrus:SenasirCompleteData';

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
        //
        global $rows_exacta,$rows_not_found,$rows_desc_mayor,$rows_desc_menor,$rows_segundo_prestamo,$prestamos_noreg,$rows_gar;
        $path = storage_path('excel/import/senasir.xls');
        Excel::selectSheetsByIndex(0)->load($path, function($reader) {
            global $rows_exacta,$rows_not_found,$rows_desc_mayor,$rows_desc_menor,$rows_segundo_prestamo,$prestamos_noreg,$rows_gar;
            $rows_exacta = Array();
            $rows_not_found = Array();
            
            array_push($rows_not_found,array('matricula','ci','app','apm', 'nom1', 'nom2'));
            array_push($rows_exacta,array('matricula','ci','app','apm', 'nom1', 'nom2'));
            
            // array_push($rows_exacta,array('Prestamos.IdPrestamo',' Prestamos.PresNumero','PresFechaDesembolso','Producto','Prestamos.PresCuotaMensual','Amortizacion.AmrFecPag','Amortizacion.AmrFecTrn','capital','Interes','Interes penal','otros cobros','Amortizacion.AmrNroCpte','Tipo pago','Amortizacion.AmrTotPag','Descuento_comando','Padron.PadMatricula',' Padron.PadCedulaIdentidad','Padron.PadMaterno',' Padron.PadPaterno',' Padron.PadNombres','Padron.PadNombres2do','Padron.Tipo','nit','ci','app','apm', 'nom1', 'nom2', 'desc_mes'));
        
            $result = $reader->select(array('matricula','paterno','materno', 'nombre',))
                        // ->take(500)     
                        ->get();
            $bar = $this->output->createProgressBar(count($result));
            $this->info(sizeof($result));
            foreach($result as $row){
                
                $matricula = trim($row->matricula);
            
                $padron = DB::table('Padron')
                                ->where('Padron.PadMatricula','like',$matricula)
                                // ->where('Padron.PadPaterno','like',trim($row->paterno).'%')
                                // ->where('Padron.PadMaterno','like',trim($row->materno).'%')
                                // ->where('Padron.PadNombres','like',trim($row->nombre).'%')
                                ->first();

                if($padron)
                {
                    array_push($rows_exacta,array($padron->PadMatricula,$padron->PadCedulaIdentidad,$padron->PadPaterno,$padron->PadMaterno,$padron->PadNombres));    
                }else{

                    array_push($rows_not_found,array($row->matricula,$row->paterno,$row->materno,$row->nombre));
                }
                
                $bar->advance();
            }
            $bar->finish();

        });

        Excel::create('senasir info',function($excel)
        {
            global $rows_exacta,$rows_not_found,$rows_desc_mayor,$rows_desc_menor,$rows_segundo_prestamo,$prestamos_noreg,$rows_gar;
            
                    $excel->sheet('encontrados',function($sheet) {
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
