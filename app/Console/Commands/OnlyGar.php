<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use DB;

class OnlyGar extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prestamos:OnlyGar';

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

        $this->info('Iniciando Modulo de Importacion');
        $path = storage_path('excel/import/comando julio oficial.xls');
        Excel::selectSheetsByIndex(0)->load($path, function($reader) {
            
            global $rows_exacta,$rows_not_found,$rows_desc_mayor,$rows_desc_menor,$rows_segundo_prestamo,$prestamos_noreg,$rows_gar;

            $rows_exacta = Array();
            $rows_segundo_prestamo = Array();
            $rows_not_found = Array();
            $rows_desc_mayor = Array();
            $rows_desc_menor = Array();
            $rows_gar = Array();
            $prestamos_noreg = Array();
            
            array_push($rows_not_found,array('nit','ci','app','apm', 'nom1', 'nom2', 'desc_mes'));
            array_push($prestamos_noreg,array('nit','ci','app','apm', 'nom1', 'nom2', 'desc_mes'));
            array_push($rows_gar,array('nit','ci','app','apm', 'nom1', 'nom2', 'desc_mes'));
            
            array_push($rows_exacta,array('Prestamos.IdPrestamo',' Prestamos.PresNumero','PresFechaDesembolso','Producto','Prestamos.PresCuotaMensual','Amortizacion.AmrFecPag','Amortizacion.AmrFecTrn','capital','Interes','Interes penal','otros cobros','Amortizacion.AmrNroCpte','Tipo pago','Amortizacion.AmrTotPag','Descuento_comando','Padron.PadMatricula',' Padron.PadCedulaIdentidad','Padron.PadMaterno',' Padron.PadPaterno',' Padron.PadNombres','Padron.PadNombres2do','Padron.Tipo'));
            array_push($rows_segundo_prestamo,array('Prestamos.IdPrestamo',' Prestamos.PresNumero','PresFechaDesembolso','Producto','Prestamos.PresCuotaMensual','Amortizacion.AmrFecPag','Amortizacion.AmrFecTrn','capital','Interes','Interes penal','otros cobros','Amortizacion.AmrNroCpte','Tipo pago','Amortizacion.AmrTotPag','Descuento_comando','Padron.PadMatricula',' Padron.PadCedulaIdentidad','Padron.PadMaterno',' Padron.PadPaterno',' Padron.PadNombres','Padron.PadNombres2do','Padron.Tipo'));
            array_push($rows_desc_mayor,array('Prestamos.IdPrestamo',' Prestamos.PresNumero','PresFechaDesembolso','Producto','Prestamos.PresCuotaMensual','Amortizacion.AmrFecPag','Amortizacion.AmrFecTrn','capital','Interes','Interes penal','otros cobros','Amortizacion.AmrNroCpte','Tipo pago','Amortizacion.AmrTotPag','Descuento_comando','Padron.PadMatricula',' Padron.PadCedulaIdentidad','Padron.PadMaterno',' Padron.PadPaterno',' Padron.PadNombres','Padron.PadNombres2do','Padron.Tipo'));
            array_push($rows_desc_menor,array('Prestamos.IdPrestamo',' Prestamos.PresNumero','PresFechaDesembolso','Producto','Prestamos.PresCuotaMensual','Amortizacion.AmrFecPag','Amortizacion.AmrFecTrn','capital','Interes','Interes penal','otros cobros','Amortizacion.AmrNroCpte','Tipo pago','Amortizacion.AmrTotPag','Descuento_comando','Padron.PadMatricula',' Padron.PadCedulaIdentidad','Padron.PadMaterno',' Padron.PadPaterno',' Padron.PadNombres','Padron.PadNombres2do','Padron.Tipo'));

        

            $result = $reader->select(array('nit','ci','app','apm', 'nom1', 'nom2', 'desc_mes'))
            // ->take(100)
             ->get();
            // reader methods
            $bar = $this->output->createProgressBar(count($result));


            foreach($result as $row){
                $ci = trim($row->ci);
                
                $descuento = floatval(str_replace(",","",$row->desc_mes));
                //$this->info($ci);
                $padron = DB::table('Padron')
                                  ->where('Padron.PadMatricula','=',''.$row->ci)
                                  ->first();
                if($padron)
                {
                    $prestamos  = DB::table('Prestamos')->where('IdPadron',$padron->IdPadron)->where('PresEstPtmo','V')->get();
                    if(sizeof($prestamos)>0)
                    {
                        array_push($prestamos_noreg,array($row->ci,$row->app,$row->apm,$row->nom1,$row->nom2,$row->desc_mes,'prestamos',sizeof($prestamos)));    
                    }
                    else{//solo garantes
                        
                        array_push($rows_gar,array($row->ci,$row->app,$row->apm,$row->nom1,$row->nom2,$row->desc_mes));  
                    }
                }
                else{

                    array_push($rows_not_found,array($row->ci,$row->app,$row->apm,$row->nom1,$row->nom2,$row->desc_mes));
                }
                
                $amortizacion = null;
                $bar->advance();
            }
            $bar->finish();
            $this->info('total rows'.$result->count());

        });

        Excel::create('clasifiacion de garantes',function($excel)
        {
            global $rows_exacta,$rows_not_found,$rows_desc_mayor,$rows_desc_menor,$rows_segundo_prestamo,$prestamos_noreg,$rows_gar;
            
                
             
                    $excel->sheet('garantes',function($sheet) {
                        global $rows_exacta,$rows_not_found,$rows_desc_mayor,$rows_desc_menor,$rows_segundo_prestamo,$prestamos_noreg,$rows_gar;
            
                            $sheet->fromModel($rows_gar,null, 'A1', false, false);
                            $sheet->cells('A1:C1', function($cells) {
                            // manipulate the range of cells
                            $cells->setBackground('#058A37');
                            $cells->setFontColor('#ffffff');  
                            $cells->setFontWeight('bold');
                            });
                        });

                    $excel->sheet('con prestamos',function($sheet) {
                        global $rows_exacta,$rows_not_found,$rows_desc_mayor,$rows_desc_menor,$rows_segundo_prestamo,$prestamos_noreg,$rows_gar;
            
                            $sheet->fromModel($prestamos_noreg,null, 'A1', false, false);
                            $sheet->cells('A1:C1', function($cells) {
                            // manipulate the range of cells
                            $cells->setBackground('#058A37');
                            $cells->setFontColor('#ffffff');  
                            $cells->setFontWeight('bold');
                            });
                        });
                    
                 
                    $excel->sheet('No amortizados',function($sheet) {
                            global $rows_exacta,$rows_not_found,$rows_desc_mayor,$rows_desc_menor;
            
                            $sheet->fromModel($rows_not_found,null, 'A1', false, false);
                            $sheet->cells('A1:C1', function($cells) {
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
