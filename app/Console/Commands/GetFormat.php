<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use DB;
class GetFormat extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prestamos:GetFormat';

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
        global $rows,$rows_not_found,$sin_amortizacion;

        $this->info("Completando informacion de la lista");
        $path = storage_path('lista_para_formato.xlsx');
        $this->info($path);
        Excel::selectSheetsByIndex(0)->load($path , function($reader) {
            
            // reader methods
            global $rows,$rows_not_found,$sin_amortizacion;
            
            // $rows = array();
            // $rows_not_found = array();
            // array_push($rows, array('nro_prestamo','fecha_desembolso','producto','matricula', 'paterno', 'materno', 'primer_nombre','segundo_nombre', 'capital','interes','interes_penal','otros_cobros','total_pagado','tipo_descuento','nro_comprobante','*','ci','paterno','materno','primer_nombre','segundo_nombre','descuento'));
            // array_push($rows_not_found, array('nro_prestamo','fecha_desembolso','producto','matricula', 'paterno', 'materno', 'primer_nombre','segundo_nombre', 'capital','interes','interes_penal','otros_cobros','total_pagado','tipo_descuento','nro_comprobante','*','ci','paterno','materno','primer_nombre','segundo_nombre','descuento'));
            // // $rows = array();
            $rows_not_found =array();
            $sin_amortizacion =array();
            $rows = array();
            array_push($sin_amortizacion,array('ci','paterno','materno','primer_nombre','segundo_nombre','descuento'));
            array_push($rows_not_found,array('ci','paterno','materno','primer_nombre','segundo_nombre','descuento'));
            array_push($rows, array('nro_prestamo','fecha_desembolso','producto','matricula', 'paterno', 'materno', 'primer_nombre','segundo_nombre', 'capital','interes','interes_penal','otros_cobros','total_pagado','tipo_descuento','nro_comprobante','*','ci','paterno','materno','primer_nombre','segundo_nombre','descuento'));   
            $result = $reader->select(array('ci','paterno','materno','primer_nombre','segundo_nombre','descuento'))
                            //->take(2)
                            ->get();
            $this->info('corriendo casos '.$result->count());
            
            $count = 0;
            foreach($result as $row){
                $padron = DB::table('Padron')->where('PadCedulaIdentidad','like', trim($row->ci))->first();
                if($padron)
                {
                    $count ++;
                    $this->info($count.'----------------------------------------------------'.$row->ci);
                    $this->info($padron->IdPadron);
                    $pre_gar = DB::table('PrestamosLevel1')->where('IdPadronGar',$padron->IdPadron)->first();
                    if($pre_gar){
                        
                        $this->info(json_encode($pre_gar));

                        $paterno = str_split( $padron->PadPaterno);
                        $sigla = 'GAR-';
                        if($paterno){
                            
                                $sigla = $sigla.trim($paterno[0]);
                            
                        }

                        $materno = str_split( $padron->PadMaterno);
                        
                        if($materno){
                            $sigla= $sigla.trim($materno[0]);
                        }
                        $nombres = str_split( $padron->PadNombres);
                        if($nombres)
                        {
                            $sigla = $sigla.trim($nombres[0]);
                        }
                        $prestamo = DB::table('Prestamos')->where('IdPrestamo',$pre_gar->IdPrestamo)->first() ;

                        $padron_titular= DB::table('Padron')->where('IdPadron',$prestamo->IdPadron)->first();

                        $amortizacion = DB::table('Amortizacion')->whereRaw("IdPrestamo = ".$prestamo->IdPrestamo." and   MONTH(AMORTIZACION.AMRFECPAG) = 06 AND YEAR(AMORTIZACION.AMRFECPAG)= 2018  and AMORTIZACION.AmrNroCpte = '".$sigla."'" )->first();
                    
                        //
                        $producto = DB::table('Producto')->where('PrdCod',$prestamo->PrdCod)->first();
                        
                        if($amortizacion)
                        {
                            
                            array_push($rows,array($prestamo->PresNumero,
                                                    $prestamo->PresFechaDesembolso,
                                                    $producto->PrdDsc,
                                                    $padron_titular->PadMatricula,
                                                    $padron_titular->PadPaterno,
                                                    $padron_titular->PadMaterno,
                                                    $padron_titular->PadNombres,
                                                    '',
                                                    $amortizacion->AmrCap,   
                                                    $amortizacion->AmrInt,   
                                                    $amortizacion->AmrIntPen,
                                                    $amortizacion->AmrOtrCob,
                                                    $amortizacion->AmrTotPag,
                                                    $amortizacion->AmrTipPAgo,
                                                    $amortizacion->AmrNroCpte,
                                                    '*',
                                                    $row->ci,
                                                    $row->paterno,
                                                    $row->materno,
                                                    $row->primer_nombre,
                                                    $row->segundo_nombre,
                                                    number_format($row->descuento, 2, ',', '')

                                                ));
                            $this->info(json_encode($amortizacion));
                        }else
                        {
                            array_push($sin_amortizacion,array($row->ci,$row->paterno,$row->materno,$row->primer_nombre,$row->segundo_nombre,$row->descuento,$prestamo->PresNumero ));
                            $this->info('not found');
                        }
                    }else
                    {
                        array_push($rows_not_found,array($row->ci,$row->paterno,$row->materno,$row->primer_nombre,$row->segundo_nombre,$row->descuento));
                        $this->info('no tiene prestamos en teoria XD');
                    }
                    
                    // $this->info();
                   // $this->info(json_encode($padron));
                }
                else{
                    $this->info('not found');
                }
                //$this->info($row);
                
            }
            $this->info('No econtrados sin prestamos -->'.sizeof($rows_not_found));
            $this->info('No econtrados sin amortizacion -->'.sizeof($sin_amortizacion));

        });

        Excel::create('lista_con_formato',function($excel)
        {
            global $rows,$rows_not_found,$sin_amortizacion; 
                    $excel->sheet('encontrados_con_amortizacion',function($sheet) {
                            global $rows,$rows_not_found,$sin_amortizacion; 
                            $sheet->fromModel($rows,null, 'A1', false, false);
                            $sheet->cells('A1:C1', function($cells) {
                            // manipulate the range of cells
                            $cells->setBackground('#058A37');
                            $cells->setFontColor('#ffffff');  
                            $cells->setFontWeight('bold');
                            });
                        });
                    $excel->sheet('encontrados_sin_amortizacion',function($sheet) {
                            global $rows,$rows_not_found,$sin_amortizacion;
                            $sheet->fromModel($rows_not_found,null, 'A1', false, false);
                            $sheet->cells('A1:C1', function($cells) {
                            // manipulate the range of cells
                            $cells->setBackground('#058A37');
                            $cells->setFontColor('#ffffff');  
                            $cells->setFontWeight('bold');
                            });
                        });
                    $excel->sheet('sin amortizaciones ',function($sheet) {
                            global $rows,$rows_not_found,$sin_amortizacion;
                            $sheet->fromModel($sin_amortizacion,null, 'A1', false, false);
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
