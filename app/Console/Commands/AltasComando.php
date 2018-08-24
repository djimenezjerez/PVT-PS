<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use DB;
class AltasComando extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prestamos:AltasComando';

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
        global $rows,$rows_not_found,$rows_garantes,$rows_pago_parcial,$rows_no_econtrados;

        $this->info(" Depurar lista de comando");
        $path = storage_path('altas_activos_20118_final.xls');
        $this->info($path);
        Excel::selectSheetsByIndex(0)->load($path , function($reader) {
            
            // reader methods
            global $rows,$rows_not_found,$rows_garantes,$rows_pago_parcial,$rows_no_econtrados;
            
            // $rows = array();
            // $rows_not_found = array();
            // array_push($rows, array('nro_prestamo','fecha_desembolso','producto','matricula', 'paterno', 'materno', 'primer_nombre','segundo_nombre', 'capital','interes','interes_penal','otros_cobros','total_pagado','tipo_descuento','nro_comprobante','*','ci','paterno','materno','primer_nombre','segundo_nombre','descuento'));
            // array_push($rows_not_found, array('nro_prestamo','fecha_desembolso','producto','matricula', 'paterno', 'materno', 'primer_nombre','segundo_nombre', 'capital','interes','interes_penal','otros_cobros','total_pagado','tipo_descuento','nro_comprobante','*','ci','paterno','materno','primer_nombre','segundo_nombre','descuento'));
            // // $rows = array();
            $rows_not_found =array();
            $rows_garantes =array();
            $rows_pago_parcial =array();
            $rows_no_econtrados = array();
            $rows = array();
            array_push($rows_garantes,array('matricula','ci','paterno','materno','primer_nombre','segundo_nombre','descuento','nro_prestamo1','cuota1','nro_prestamo2','cuota2','prestamos garantes'));
            array_push($rows_pago_parcial,array('matricula','ci','paterno','materno','primer_nombre','segundo_nombre','nro_prestamo','cuota','estado','descuento'));
            array_push($rows_not_found,array('ci','paterno','materno','primer_nombre','segundo_nombre','descuento'));
            array_push($rows,array('matricula','ci','paterno','materno','primer_nombre','segundo_nombre','renta','tipo','descuento'));   
            $result = $reader->select(array('ci','paterno','materno','primer_nombre','segundo_nombre','descuento'))
                           // ->take(100)
                            ->get();
            $this->info('corriendo casos '.$result->count());
            
            $count = 0;
            foreach($result as $row){
                
                //$desc = floatval(str_replace(",","",$row->descuento));
            
                $padron = DB::table('Padron')
                             
                              ->where('Padron.PadMatricula','like',''.(int) trim($row->ci))
                              ->select('Padron.IdPadron','Padron.PadMatricula','Padron.PadCedulaIdentidad','Padron.PadPaterno','Padron.PadMaterno','Padron.PadNombres','Padron.PadNombres2do','Padron.PadTipRentAFPSENASIR','Padron.PadTipo')
                              ->first();

            
                // $padron =  DB::table('Padron')->where('PadMatricula','=',''.(int) $row->ci)->first();
                if($padron)
                {
                    array_push($rows,array($padron->PadMatricula,$padron->PadCedulaIdentidad,utf8_encode($padron->PadPaterno),utf8_encode($padron->PadMaterno),utf8_encode($padron->PadNombres),utf8_encode($padron->PadNombres2do),$padron->PadTipo,$padron->PadTipRentAFPSENASIR,$row->descuento));
                    $this->info('Matricula ='.$padron->PadMatricula.' found');
                }
                else{
                 $padron_l = DB::table('Padron')->where('PadMatricula','like',''.(int) trim($row->ci).'%')->first();
                 if($padron_l)
                 {
                    array_push($rows,array($padron_l->PadMatricula,$padron_l->PadCedulaIdentidad,utf8_encode($padron_l->PadPaterno),utf8_encode($padron_l->PadMaterno),utf8_encode($padron_l->PadNombres),utf8_encode($padron_l->PadNombres2do),$padron_l->PadTipo,$padron_l->PadTipRentAFPSENASIR,$row->descuento));
                    $this->info('Matricula ='.$padron_l->PadMatricula.' found l');
                 }else{
                     array_push($rows_not_found,array($row->ci,$row->paterno,$row->materno,$row->primer_nombre,$row->segundo_nombre,$row->descuento));
                     $this->info('Matricula ='.$row->ci.' found');
                 }
                }
    
                
            }
        
            
            $this->info('row -->'.sizeof($rows));
            $this->info('row solo garantes -->'.sizeof($rows_not_found));
       

        });

        Excel::create('comando_activo_2018_full',function($excel)
        {
            global $rows,$rows_not_found,$rows_garantes,$rows_pago_parcial,$rows_no_econtrados;
            
                    $excel->sheet('encontrados',function($sheet) {
                             global $rows,$rows_not_found,$rows_garantes;
            
                            $sheet->fromModel($rows,null, 'A1', false, false);
                            $sheet->cells('A1:C1', function($cells) {
                            // manipulate the range of cells
                            $cells->setBackground('#058A37');
                            $cells->setFontColor('#ffffff');  
                            $cells->setFontWeight('bold');
                            });
                        });

                    // $excel->sheet('rebuscados',function($sheet) {
                    //          global $rows,$rows_not_found,$rows_pago_parcial;
            
                    //         $sheet->fromModel($rows_no_econtrados,null, 'A1', false, false);
                    //         $sheet->cells('A1:C1', function($cells) {
                    //         // manipulate the range of cells
                    //         $cells->setBackground('#058A37');
                    //         $cells->setFontColor('#ffffff');  
                    //         $cells->setFontWeight('bold');
                    //         });
                    //     });
                    $excel->sheet('no encontrados',function($sheet) {
                             global $rows,$rows_not_found,$rows_no_econtrados;
            
                            $sheet->fromModel($rows_not_found,null, 'A1', false, false);
                            $sheet->cells('A1:C1', function($cells) {
                            // manipulate the range of cells
                            $cells->setBackground('#058A37');
                            $cells->setFontColor('#ffffff');  
                            $cells->setFontWeight('bold');
                            });
                        });
                  
        })->store('xls', storage_path());
        $this->info('Finished');
    }
}
