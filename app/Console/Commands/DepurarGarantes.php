<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use DB;
use Log;
class DepurarGarantes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prestamos:DepurarGarantes';

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
        global $rows_exacta,$rows_not_found,$rows_desc_mayor,$rows_desc_menor,$rows_segundo_prestamo,$rows_indebidos,$rows_gar;

        $this->info('Iniciando Modulo de Importacion');
        $path = storage_path('excel/export/clasifiacion de garantes.xls');
        Excel::selectSheetsByIndex(0)->load($path, function($reader) {
            
            global $rows_exacta,$rows_not_found,$rows_desc_mayor,$rows_desc_menor,$rows_segundo_prestamo,$rows_indebidos,$rows_gar;

            $rows_exacta = Array();
            $rows_segundo_prestamo = Array();
            $rows_not_found = Array();
            $rows_desc_mayor = Array();
            $rows_desc_menor = Array();
            $rows_gar = Array();
            $rows_indebidos = Array();
            
            array_push($rows_not_found,array('nit','ci','app','apm', 'nom1', 'nom2', 'desc_mes'));
            array_push($rows_indebidos,array('nit','ci','app','apm', 'nom1', 'nom2', 'desc_mes'));
            array_push($rows_gar,array('nit','ci','app','apm', 'nom1', 'nom2', 'desc_mes'));
            
            array_push($rows_exacta,array('Prestamos.IdPrestamo',' Prestamos.PresNumero','PresFechaDesembolso','Producto','Prestamos.PresCuotaMensual','Amortizacion.AmrFecPag','Amortizacion.AmrFecTrn','capital','Interes','Interes penal','otros cobros','Amortizacion.AmrNroCpte','Tipo pago','Amortizacion.AmrTotPag','Descuento_comando','Padron.PadMatricula',' Padron.PadCedulaIdentidad','Padron.PadMaterno',' Padron.PadPaterno',' Padron.PadNombres','Padron.PadNombres2do','Padron.Tipo','nit','ci','app','apm', 'nom1', 'nom2', 'desc_mes'));
            array_push($rows_segundo_prestamo,array('Prestamos.IdPrestamo',' Prestamos.PresNumero','PresFechaDesembolso','Producto','Prestamos.PresCuotaMensual','Amortizacion.AmrFecPag','Amortizacion.AmrFecTrn','capital','Interes','Interes penal','otros cobros','Amortizacion.AmrNroCpte','Tipo pago','Amortizacion.AmrTotPag','Descuento_comando','Padron.PadMatricula',' Padron.PadCedulaIdentidad','Padron.PadMaterno',' Padron.PadPaterno',' Padron.PadNombres','Padron.PadNombres2do','Padron.Tipo','nit','ci','app','apm', 'nom1', 'nom2', 'desc_mes'));
            array_push($rows_desc_mayor,array('Prestamos.IdPrestamo',' Prestamos.PresNumero','PresFechaDesembolso','Producto','Prestamos.PresCuotaMensual','Amortizacion.AmrFecPag','Amortizacion.AmrFecTrn','capital','Interes','Interes penal','otros cobros','Amortizacion.AmrNroCpte','Tipo pago','Amortizacion.AmrTotPag','Descuento_comando','Padron.PadMatricula',' Padron.PadCedulaIdentidad','Padron.PadMaterno',' Padron.PadPaterno',' Padron.PadNombres','Padron.PadNombres2do','Padron.Tipo','nit','ci','app','apm', 'nom1', 'nom2', 'desc_mes'));
            array_push($rows_desc_menor,array('Prestamos.IdPrestamo',' Prestamos.PresNumero','PresFechaDesembolso','Producto','Prestamos.PresCuotaMensual','Amortizacion.AmrFecPag','Amortizacion.AmrFecTrn','capital','Interes','Interes penal','otros cobros','Amortizacion.AmrNroCpte','Tipo pago','Amortizacion.AmrTotPag','Descuento_comando','Padron.PadMatricula',' Padron.PadCedulaIdentidad','Padron.PadMaterno',' Padron.PadPaterno',' Padron.PadNombres','Padron.PadNombres2do','Padron.Tipo','nit','ci','app','apm', 'nom1', 'nom2', 'desc_mes'));

        

            $result = $reader->select(array('nit','ci','app','apm', 'nom1', 'nom2', 'desc_mes'))
           // ->take(2)
             ->get();
            // reader methods
            $bar = $this->output->createProgressBar(count($result));


            foreach($result as $row){
                $ci = trim($row->ci);
                $descuento = floatval($row->desc_mes);
                //$this->info($ci);
                //buscar prestamos garantizados

                $padron = DB::table('Padron')->where('PadCedulaIdentidad','=', $row->ci)->first();
                if($padron)
                {

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
                    Log::info($sigla);

                    //revisar a quien se garantiza

                    $prestamos_garantizados = DB::table('PrestamosLevel1')->where('IdPadronGar','=',$padron->IdPadron)->get();
                    
                    if(sizeOf($prestamos_garantizados)>0)
                    {
                        //$gar_amorizado = false;
                        foreach($prestamos_garantizados as $pres_gar)
                        {
                            if($descuento>0)
                            {
                                
                                $prestamo = DB::table('Prestamos')->where('Prestamos.PresEstPtmo','=','V')->where('Prestamos.IdPrestamo','=',$pres_gar->IdPrestamo)->first();
                                
                                if($prestamo)//si es vigente ver si es garante de este 
                                {
                                    
                                    $amr_verificada = DB::table('Amortizacion')->where('AmrNroCpte','=',$sigla)->where('IdPrestamo',$prestamo->IdPrestamo)->first();
                                    $padron_gar = DB::table('Padron')->where('IdPadron','=',$padron->IdPadron)->first();
                                    $producto = DB::table('Producto')->where('PrdCod','=',$prestamo->PrdCod)->first();
                                  //  Log::info(json_encode($amr_verificada));
                                    if($amr_verificada)//si es garante de este tramite
                                    {
                                        Log::info('Verificado '.$sigla.'.');
                                        //buscar la amortizacion del mes correspondiente
                                        $amortizacion =  DB::table('Amortizacion')->where('IdPrestamo',$prestamo->IdPrestamo)
                                                                                  ->whereRaw('DAY(Amortizacion.AmrFecPag) = 30 and MONTH(Amortizacion.AmrFecPag) = 6 and YEAR(Amortizacion.AmrFecPag) = 2018')
                                                                                  ->where('Amortizacion.AmrSts','!=','X')
                                                                                  ->where('Amortizacion.AmrNroCpte','=',$sigla)
                                                                                  ->first();
                                        if($amortizacion)
                                        {
                                            $descuento = round($descuento,2) - round($amortizacion->AmrTotPag,2);
                                            array_push($rows_exacta,array($prestamo->IdPrestamo,$prestamo->PresNumero,$prestamo->PresFechaDesembolso,$producto->PrdDsc,$prestamo->PresCuotaMensual,$amortizacion->AmrFecPag,$amortizacion->AmrFecTrn,$amortizacion->AmrCap,$amortizacion->AmrInt,$amortizacion->AmrIntPen,$amortizacion->AmrOtrCob,$amortizacion->AmrNroCpte,$amortizacion->AmrTipPAgo,$amortizacion->AmrTotPag,$row->desc_mes,$padron_gar->PadMatricula,$padron_gar->PadCedulaIdentidad,utf8_encode($padron_gar->PadMaterno),utf8_encode($padron_gar->PadPaterno),utf8_encode($padron_gar->PadNombres),utf8_encode($padron_gar->PadNombres2do),$padron_gar->PadTipo,'*',$row->nit,$row->ci,$row->app,$row->apm,$row->nom1,$row->nom2,$descuento));
                                        }else{
                                            array_push($rows_gar,array($row->nit,$row->ci,$row->app,$row->apm,$row->nom1,$row->nom2,$descuento,$prestamo->PresNumero,$sigla,$amr_verificada->AmrTotPag)); 
                                        }
                                        // }else{//debe garantizarse
    
                                        //     array_push($rows_gar,array($row->nit,$row->ci,$row->app,$row->apm,$row->nom1,$row->nom2,$descuento,$prestamo->PresNumero,$sigla,$amr_verificada->AmrTotPag)); 
                                        // }        
                                        Log::info(json_encode($amortizacion));
                                        // $amortizacion2 = DB::table('Amortizacion')//amortizacion del garante
                                        //                 ->join('Prestamos','Prestamos.IdPrestamo','=','Amortizacion.IdPrestamo')
                                        //                 ->join('Padron','Padron.IdPadron','=','Prestamos.IdPadron')
                                        //                 ->join('Producto','Producto.PrdCod','=','Prestamos.PrdCod')
                                        //                 ->where('Padron.PadTipo','=','ACTIVO')
                                        //                 ->where('Prestamos.PresEstPtmo','=','V')
                                        //                 ->where('Amortizacion.AmrNroCpte','=',$sigla)
                                        //                 ->where('Amortizacion.AmrSts','!=','X')
                                        //                 ->where('Prestamos.IdPrestamo','!=',$prestamo->IdPrestamo)
                                        //                 ->first();
                                        
                                        // if($amortizacion2)
                                        // {
                                        //     //$gar_amorizado = true;
                                        //     $descuento = round($descuento,2) - round($amortizacion2->AmrTotPag,2);
                                        //     //$this->info($amortizacion->AmrTotPag.' prestamo 2  sobrante'.$descuento);
                                        //     array_push($rows_exacta,array($amortizacion2->IdPrestamo,$amortizacion2->PresNumero,$amortizacion2->PresFechaDesembolso,$amortizacion2->PrdDsc,$amortizacion2->PresCuotaMensual,$amortizacion2->AmrFecPag,$amortizacion2->AmrFecTrn,$amortizacion2->AmrCap,$amortizacion2->AmrInt,$amortizacion2->AmrIntPen,$amortizacion2->AmrOtrCob,$amortizacion2->AmrNroCpte,$amortizacion2->AmrTipPAgo,$amortizacion2->AmrTotPag,$row->desc_mes,$amortizacion2->PadMatricula,$amortizacion2->PadCedulaIdentidad,utf8_encode($amortizacion2->PadMaterno),utf8_encode($amortizacion2->PadPaterno),utf8_encode($amortizacion2->PadNombres),utf8_encode($amortizacion2->PadNombres2do),$amortizacion2->PadTipo,'*',$row->nit,$row->ci,$row->app,$row->apm,$row->nom1,$row->nom2,$descuento));
                                            
                                        
                                    }
                                }
                            }

                        }
                    }else{

                        array_push($rows_indebidos,array($row->nit,$row->ci,$row->app,$row->apm,$row->nom1,$row->nom2,$row->desc_mes));
                    }

                }else{//en caso de no encontrar 
                    array_push($rows_not_found,array($row->nit,$row->ci,$row->app,$row->apm,$row->nom1,$row->nom2,$row->desc_mes));
                }


                $amortizacion = null;
                $bar->advance();
            }
            $bar->finish();
            $this->info('total rows'.$result->count());

        });

        Excel::create('garantes depurados',function($excel)
        {
            global $rows_exacta,$rows_not_found,$rows_desc_mayor,$rows_desc_menor,$rows_segundo_prestamo,$rows_indebidos,$rows_gar;
            
                    $excel->sheet('prestamos garantizados',function($sheet) {
                            global $rows_exacta,$rows_not_found,$rows_desc_mayor,$rows_desc_menor;
            
                            $sheet->fromModel($rows_exacta,null, 'A1', false, false);
                            $sheet->cells('A1:C1', function($cells) {
                            // manipulate the range of cells
                            $cells->setBackground('#058A37');
                            $cells->setFontColor('#ffffff');  
                            $cells->setFontWeight('bold');
                            });
                        });
                  
                   
                    $excel->sheet('noreg posibles demasias',function($sheet) {
                        global $rows_exacta,$rows_not_found,$rows_desc_mayor,$rows_desc_menor,$rows_segundo_prestamo,$rows_indebidos,$rows_gar;
            
                            $sheet->fromModel($rows_gar,null, 'A1', false, false);
                            $sheet->cells('A1:C1', function($cells) {
                            // manipulate the range of cells
                            $cells->setBackground('#058A37');
                            $cells->setFontColor('#ffffff');  
                            $cells->setFontWeight('bold');
                            });
                        });
                    $excel->sheet('sin prestamos ',function($sheet) {
                        global $rows_exacta,$rows_not_found,$rows_desc_mayor,$rows_desc_menor,$rows_segundo_prestamo,$rows_indebidos,$rows_gar;
            
                            $sheet->fromModel($rows_indebidos,null, 'A1', false, false);
                            $sheet->cells('A1:C1', function($cells) {
                            // manipulate the range of cells
                            $cells->setBackground('#058A37');
                            $cells->setFontColor('#ffffff');  
                            $cells->setFontWeight('bold');
                            });
                        });
                    
                 
                    $excel->sheet('No encontrados',function($sheet) {
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
