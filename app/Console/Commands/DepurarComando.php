<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use DB;
class DepurarComando extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prestamos:DepurarComando';

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
        $path = storage_path('comando_julio_2018.xls');
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
            array_push($rows,array('matricula','ci','paterno','materno','primer_nombre','segundo_nombre','nro_prestamo','cuota','estado','descuento'));   
            $result = $reader->select(array('ci','paterno','materno','primer_nombre','segundo_nombre','descuento'))
                           // ->take(100)
                            ->get();
            $this->info('corriendo casos '.$result->count());
            
            $count = 0;
            foreach($result as $row){
                
                //$desc = floatval(str_replace(",","",$row->descuento));
            
                

                $padron = DB::table('Padron')
                              ->join('Prestamos','Prestamos.IdPadron','=','Padron.IdPadron')
                              ->where('Padron.PadMatricula','=',''.(int) $row->ci)
                              ->where('Prestamos.PresEstPtmo','=','V')
                              ->select('Padron.IdPadron','Padron.PadMatricula','Padron.PadCedulaIdentidad','Padron.PadPaterno','Padron.PadMaterno','Padron.PadNombres','Padron.PadNombres2do','Prestamos.PresNumero','Prestamos.PresCuotaMensual','Prestamos.PresEstPtmo')
                              ->first();
                // $padron =  DB::table('Padron')->where('PadMatricula','=',''.(int) $row->ci)->first();
                if($padron)
                {
                    $descuento = floatval(str_replace(",","",$row->descuento));
                    if($padron->PresCuotaMensual == $descuento)
                    {
                        array_push($rows,array($padron->PadMatricula,$padron->PadCedulaIdentidad,$padron->PadPaterno,$padron->PadMaterno,$padron->PadNombres,$padron->PadNombres2do,$padron->PresNumero,$padron->PresCuotaMensual,$padron->PresEstPtmo,$row->descuento));
                    }
                    else{
                       if($padron->PresCuotaMensual > $descuento)
                       {
                           array_push($rows_pago_parcial,array($padron->PadMatricula,$padron->PadCedulaIdentidad,$padron->PadPaterno,$padron->PadMaterno,$padron->PadNombres,$padron->PadNombres2do,$padron->PresNumero,$padron->PresCuotaMensual,$padron->PresEstPtmo,$row->descuento));
                       }else{
                           
                            //con garantes sacar prestamos vigentes

                           $prestamos_gar = DB::table('PrestamosLevel1')
                                            ->join('Prestamos','Prestamos.IdPrestamo','=','PrestamosLevel1.IdPrestamo')
                                            ->where('PrestamosLevel1.IdPadronGar','=',$padron->IdPadron)
                                            ->where('Prestamos.PresEstPtmo','=','V')
                                            ->select('Prestamos.PresNumero','Prestamos.PresCuotaMensual')
                                            ->get();
                            $prestamos = DB::table('Prestamos')->where('IdPadron',$padron->IdPadron)->where('PresEstPtmo','V')->get();
                            $r_pg = array($padron->PadMatricula,$padron->PadCedulaIdentidad,$padron->PadPaterno,$padron->PadMaterno,$padron->PadNombres,$padron->PadNombres2do,$row->descuento);
                            if($prestamos)
                            {   
                                foreach($prestamos as $prestamo)
                                {
                                    array_push($r_pg, $prestamo->PresNumero,$prestamo->PresCuotaMensual);
                                }
                            }else
                            {
                                array_push($r_pg,"","","","");
                            }
                            
                            if($prestamos_gar)
                            {
                                foreach($prestamos_gar as $rp)
                                {
                                    array_push($r_pg,$rp->PresNumero,$rp->PresCuotaMensual);
                                }
                            }
                           array_push($rows_garantes,$r_pg);
                       }                        
                    }
                }
                else{
                    $padron_g = DB::table('Padron')
                                  ->where('Padron.PadMatricula','=',''.(int) $row->ci)
                                  ->first();
                    if($padron_g){
                        $prestamos_gar = DB::table('PrestamosLevel1')
                        ->join('Prestamos','Prestamos.IdPrestamo','=','PrestamosLevel1.IdPrestamo')
                        ->where('PrestamosLevel1.IdPadronGar','=',$padron_g->IdPadron)
                        ->where('Prestamos.PresEstPtmo','=','V')
                        ->select('Prestamos.PresNumero','Prestamos.PresCuotaMensual')
                        ->get();
                        $garantes = array($padron_g->PadMatricula,$padron_g->PadCedulaIdentidad,$padron_g->PadPaterno,$padron_g->PadMaterno,$padron_g->PadNombres,$padron_g->PadNombres2do,$row->descuento);
                        
                        if($prestamos_gar)
                        {
                            foreach($prestamos_gar as $rp)
                            {
                                array_push($garantes,$rp->PresNumero,$rp->PresCuotaMensual);
                            }
                        }
                        array_push($rows_not_found,$garantes);
                    }else{
                        $this->info('no entonctrado '.$row->ci);
                        array_push($rows_no_econtrados,array($row->ci,$row->paterno,$row->materno,$row->primer_nombre,$row->segundo_nombre,$row->descuento));
                        ///$count++;
                    }       

                }
                $this->info(json_encode($padron));
                
            }
            
            $this->info('row -->'.sizeof($rows));
            $this->info('row solo garantes -->'.sizeof($rows_not_found));
            $this->info('row con garantes -->'.sizeof($rows_garantes));
            //$this->info('count '.$count);
            $this->info("no econtrados");
            // foreach($rows_no_econtrados as $sn)
            // {
            //     $this->info("--".$sn."--");
            // }

        });

        Excel::create('comando_depurados_julio_2018',function($excel)
        {
            global $rows,$rows_not_found,$rows_garantes,$rows_pago_parcial,$rows_no_econtrados;
            
                    $excel->sheet('con prestamos',function($sheet) {
                             global $rows,$rows_not_found,$rows_garantes;
            
                            $sheet->fromModel($rows,null, 'A1', false, false);
                            $sheet->cells('A1:C1', function($cells) {
                            // manipulate the range of cells
                            $cells->setBackground('#058A37');
                            $cells->setFontColor('#ffffff');  
                            $cells->setFontWeight('bold');
                            });
                        });

                    $excel->sheet('pago parcial',function($sheet) {
                             global $rows,$rows_not_found,$rows_pago_parcial;
            
                            $sheet->fromModel($rows_pago_parcial,null, 'A1', false, false);
                            $sheet->cells('A1:C1', function($cells) {
                            // manipulate the range of cells
                            $cells->setBackground('#058A37');
                            $cells->setFontColor('#ffffff');  
                            $cells->setFontWeight('bold');
                            });
                        });
                    $excel->sheet('con prestamos gar',function($sheet) {
                             global $rows,$rows_not_found,$rows_garantes;
            
                            $sheet->fromModel($rows_garantes,null, 'A1', false, false);
                            $sheet->cells('A1:C1', function($cells) {
                            // manipulate the range of cells
                            $cells->setBackground('#058A37');
                            $cells->setFontColor('#ffffff');  
                            $cells->setFontWeight('bold');
                            });
                        });

                    $excel->sheet('solo gar',function($sheet) {
                             global $rows,$rows_not_found,$rows_garantes;
            
                            $sheet->fromModel($rows_not_found,null, 'A1', false, false);
                            $sheet->cells('A1:C1', function($cells) {
                            // manipulate the range of cells
                            $cells->setBackground('#058A37');
                            $cells->setFontColor('#ffffff');  
                            $cells->setFontWeight('bold');
                            });
                        });
                    $excel->sheet('solo gar',function($sheet) {
                             global $rows,$rows_not_found,$rows_no_econtrados;
            
                            $sheet->fromModel($rows_no_econtrados,null, 'A1', false, false);
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
