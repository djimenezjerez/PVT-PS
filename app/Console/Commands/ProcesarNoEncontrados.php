<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use DB;

class ProcesarNoEncontrados extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prestamos:ProcesarNoEncontrados';

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
        $this->info("Segundo Tratamiento XD");
        global $rows_exacta,$rows_not_found,$rows_desc_mayor,$rows_desc_menor;

        $this->info('Iniciando Modulo de Importacion');
        $path = storage_path('excel/import/no encontrados julio.xls');
        Excel::selectSheetsByIndex(2)->load($path, function($reader) {
            
            global $rows_exacta,$rows_not_found,$rows_noreg,$rows_exdentes,$rows_indebidos,$rows_gar,$rows_gar_noreg,$rows_pres_noreg;

            $rows_exacta = Array();
            $rows_not_found = Array();
            $rows_noreg = Array();
            $rows_exdentes = Array();
            $rows_indebidos = Array();
            $rows_gar = Array();
            $rows_gar_noreg = Array();
            $rows_pres_noreg = Array();
            
            array_push($rows_not_found,array('nit','ci','app','apm', 'nom1', 'nom2', 'desc_mes'));
            array_push($rows_indebidos,array('nit','ci','app','apm', 'nom1', 'nom2', 'desc_mes'));
            array_push($rows_gar_noreg,array('nit','ci','app','apm', 'nom1', 'nom2', 'desc_mes'));
            array_push($rows_noreg,array('nit','ci','app','apm', 'nom1', 'nom2', 'desc_mes'));
            array_push($rows_exdentes,array('nit','ci','app','apm', 'nom1', 'nom2', 'desc_mes'));
            array_push($rows_pres_noreg,array('nit','ci','app','apm', 'nom1', 'nom2', 'desc_mes'));
            
            array_push($rows_exacta,array('Prestamos.IdPrestamo',' Prestamos.PresNumero','PresFechaDesembolso','Producto','Prestamos.PresCuotaMensual','Amortizacion.AmrFecPag','Amortizacion.AmrFecTrn','capital','Interes','Interes penal','otros cobros','Amortizacion.AmrNroCpte','Tipo pago','Amortizacion.AmrTotPag','Descuento_comando','Padron.PadMatricula',' Padron.PadCedulaIdentidad','Padron.PadMaterno',' Padron.PadPaterno',' Padron.PadNombres','Padron.PadNombres2do','Padron.Tipo'));
           // array_push($rows_exdentes,array('Prestamos.IdPrestamo',' Prestamos.PresNumero','PresFechaDesembolso','Producto','Prestamos.PresCuotaMensual','Amortizacion.AmrFecPag','Amortizacion.AmrFecTrn','capital','Interes','Interes penal','otros cobros','Amortizacion.AmrNroCpte','Tipo pago','Amortizacion.AmrTotPag','Descuento_comando','Padron.PadMatricula',' Padron.PadCedulaIdentidad','Padron.PadMaterno',' Padron.PadPaterno',' Padron.PadNombres','Padron.PadNombres2do','Padron.Tipo'));
            array_push($rows_gar,array('Prestamos.IdPrestamo',' Prestamos.PresNumero','PresFechaDesembolso','Producto','Prestamos.PresCuotaMensual','Amortizacion.AmrFecPag','Amortizacion.AmrFecTrn','capital','Interes','Interes penal','otros cobros','Amortizacion.AmrNroCpte','Tipo pago','Amortizacion.AmrTotPag','Descuento_comando','Padron.PadMatricula',' Padron.PadCedulaIdentidad','Padron.PadMaterno',' Padron.PadPaterno',' Padron.PadNombres','Padron.PadNombres2do','Padron.Tipo'));

        

            $result = $reader->select(array('nit','ci','app','apm', 'nom1', 'nom2', 'desc_mes'))
             //->take(1)
             ->get();
            // reader methods
            foreach($result as $row){
                $ci = trim($row->ci);
                $descuento = floatval(str_replace(",","",$row->desc_mes));
                $this->info($ci);

                $padron = DB::table('Padron')
                              ->where('PadMatricula','=',''.$ci)
                              ->first();
                if($padron)
                {
                    $this->info($padron->PadCedulaIdentidad.' found');

                    // buscando prestamos
                    $this->info($padron->IdPadron);
                    $prestamos = DB::table('Prestamos')
                                    ->where('Prestamos.IdPadron','=',$padron->IdPadron)
                                    ->where('PresEstPtmo','=','V')
                                    ->get();
                    //$this->info(json_encode($prestamos));
                    if(sizeOf($prestamos)>0)
                    {
                        //verificar los siguientes casos : 1 prestamo 1 garante, 2 prestamos, 2 prestamos 1 garante, 2 prestamos 2 garantes XD
                       foreach($prestamos as $prestamo){

                            $amortizacion = DB::table('Amortizacion')
                                            ->join('Prestamos','Prestamos.IdPrestamo','=','Amortizacion.IdPrestamo')
                                            ->join('Padron','Padron.IdPadron','=','Prestamos.IdPadron')
                                            ->join('Producto','Producto.PrdCod','=','Prestamos.PrdCod')
                                            ->where('Padron.PadTipo','=','ACTIVO')
                                            ->where('Prestamos.IdPrestamo','=',$prestamo->IdPrestamo)
                                            ->where('Amortizacion.AmrNroCpte','=','D-07/18')
                                            ->where('Amortizacion.AmrSts','!=','X')
                                            ->where('Padron.IdPadron','=',$padron->IdPadron)
                                            ->first();
                            if($amortizacion)
                            {
                                $descuento = $descuento - $amortizacion->AmrTotPag;
                                array_push($rows_exacta,array($amortizacion->IdPrestamo,$amortizacion->PresNumero,$amortizacion->PresFechaDesembolso,$amortizacion->PrdDsc,$amortizacion->PresCuotaMensual,$amortizacion->AmrFecPag,$amortizacion->AmrFecTrn,$amortizacion->AmrCap,$amortizacion->AmrInt,$amortizacion->AmrIntPen,$amortizacion->AmrOtrCob,$amortizacion->AmrNroCpte,$amortizacion->AmrTipPAgo,$amortizacion->AmrTotPag,$descuento,$amortizacion->PadMatricula,$amortizacion->PadCedulaIdentidad,utf8_encode($amortizacion->PadMaterno),utf8_encode($amortizacion->PadPaterno),utf8_encode($amortizacion->PadNombres),utf8_encode($amortizacion->PadNombres2do),$amortizacion->PadTipo));
                            }
                            else
                            {
                                //ver en caso de ser primera cuota plan de pagos hdps
                                $descuento = $descuento - $prestamo->PresCuotaMensual;
                               if($descuento >0)
                               {
                                   array_push($rows_pres_noreg,array($row->nit,$row->ci,$row->app,$row->apm,$row->nom1,$row->nom2,$descuento,$prestamo->PresNumero,$prestamo->PresCuotaMensual));
                               } 
                               
                            }

                       }
                       if($descuento>0)//ver garantizados
                       {
                          //ver modulo de busqueda a travez de garante hdp
                          array_push($rows_exdentes,array($row->nit,$row->ci,$row->app,$row->apm,$row->nom1,$row->nom2,$descuento));
                       }// garantizados hdps


                    }else{ //solo es garante
                        //sigla
                        $this->info("buscando prestamos garantizados");
                        $sigla = 'GAR-';
                        $paterno = str_split( $padron->PadPaterno);
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
                        $this->info("Sigla ".$sigla);
                        //verificar a quien esta garantizando XD 
                        $garantizos = DB::table('PrestamosLevel1')
                                    ->where('IdPadronGar','=',$padron->IdPadron)
                                    ->get();
                        if(sizeOf($garantizos)>0)
                        {
                            
                            $descuento_prestamos = 0;
                            foreach($garantizos as $garantizo){
                                $prestamo = DB::table('Prestamos')->where('IdPrestamo',$garantizo->IdPrestamo)->where('PresEstPtmo','=','V')->first();
                                if($prestamo)
                                {
                                    //$descuento_prestamos += $prestamo->PresCuotaMensual;
                                    $amortizacion = DB::table('Amortizacion')
                                                            ->whereRaw(' DAY(Amortizacion.AmrFecPag) = 31 and MONTH(Amortizacion.AmrFecPag) = 7 and YEAR(Amortizacion.AmrFecPag) = 2018')
                                                            ->where('Amortizacion.AmrSts','!=','X')
                                                            ->where('Amortizacion.AmrNroCpte ','=',$sigla)
                                                            ->first();
                                    if($amortizacion)
                                    {
                                        $this->info("con amortizacion");
                                        $descuento = $descuento - $amortizacion->AmrTotPag;
                                        $producto = DB::table('Producto')->where('PrdCod',$prestamo->PrdCod)->first();
                                        $padron_titular = DB::table('Padron')->where('IdPadron',$prestamo->IdPadron)->first();
                                        array_push($rows_gar,array($amortizacion->IdPrestamo,$prestamo->PresNumero,$prestamo->PresFechaDesembolso,$producto->PrdDsc,$prestamo->PresCuotaMensual,$amortizacion->AmrFecPag,$amortizacion->AmrFecTrn,$amortizacion->AmrCap,$amortizacion->AmrInt,$amortizacion->AmrIntPen,$amortizacion->AmrOtrCob,$amortizacion->AmrNroCpte,$amortizacion->AmrTipPAgo,$amortizacion->AmrTotPag,$descuento,$padron_titular->PadMatricula,$padron_titular->PadCedulaIdentidad,utf8_encode($padron_titular->PadMaterno),utf8_encode($padron_titular->PadPaterno),utf8_encode($padron_titular->PadNombres),utf8_encode($padron_titular->PadNombres2do),$padron_titular->PadTipo,'*',$row->nit,$row->ci,$row->app,$row->apm,$row->nom1,$row->nom2,$row->desc_mes));
                                    }
                                    else{

                                        if($descuento>0)
                                        {
                                            array_push($rows_gar_noreg,array($row->nit,$row->ci,$row->app,$row->apm,$row->nom1,$row->nom2,$descuento,$prestamo->PresNumero));
                                        }
                                    }
                                }
                            }
                           
                        }
                        else{//cobro indebido
                            $this->info($ci.' cobro indevido :V' );
                            array_push($rows_indebidos,array($row->nit,$row->ci,$row->app,$row->apm,$row->nom1,$row->nom2,$row->desc_mes));
                        }
                    }


                }else{
                    $this->info($ci.'not found   ----------- :(' );
                    array_push($rows_not_found,array($row->nit,$row->ci,$row->app,$row->apm,$row->nom1,$row->nom2,$row->desc_mes));
                }          
               

            }
            
            $this->info('total rows'.$result->count());
            $this->info('total gar '.sizeOf($rows_gar));
            $this->info('total gar noreg'.sizeOf($rows_gar_noreg));
            $this->info('total :( '.sizeOf($rows_not_found));
            $this->info('total :v '.sizeOf($rows_indebidos));



        });


        Excel::create('Segundo Tratamiento XD',function($excel)
        {
            global $rows_exacta,$rows_not_found,$rows_noreg,$rows_exdentes,$rows_indebidos,$rows_gar,$rows_gar_noreg,$rows_pres_noreg;
            
                    $excel->sheet('Descuentos exactos',function($sheet) {
                        global $rows_exacta,$rows_not_found,$rows_noreg,$rows_exdentes,$rows_indebidos,$rows_gar,$rows_gar_noreg;
            
                            $sheet->fromModel($rows_exacta,null, 'A1', false, false);
                            $sheet->cells('A1:C1', function($cells) {
                            // manipulate the range of cells
                            $cells->setBackground('#058A37');
                            $cells->setFontColor('#ffffff');  
                            $cells->setFontWeight('bold');
                            });
                        });
                    $excel->sheet('gar',function($sheet) {
                        global $rows_exacta,$rows_not_found,$rows_noreg,$rows_exdentes,$rows_indebidos,$rows_gar,$rows_gar_noreg;
            
                            $sheet->fromModel($rows_gar,null, 'A1', false, false);
                            $sheet->cells('A1:C1', function($cells) {
                            // manipulate the range of cells
                            $cells->setBackground('#058A37');
                            $cells->setFontColor('#ffffff');  
                            $cells->setFontWeight('bold');
                            });
                        });
                    
                    $excel->sheet('gar noreg',function($sheet) {
                        global $rows_exacta,$rows_not_found,$rows_noreg,$rows_exdentes,$rows_indebidos,$rows_gar,$rows_gar_noreg;
            
                            $sheet->fromModel($rows_gar_noreg,null, 'A1', false, false);
                            $sheet->cells('A1:C1', function($cells) {
                            // manipulate the range of cells
                            $cells->setBackground('#058A37');
                            $cells->setFontColor('#ffffff');  
                            $cells->setFontWeight('bold');
                            });
                        });
                    $excel->sheet('indevidos',function($sheet) {
                        global $rows_exacta,$rows_not_found,$rows_noreg,$rows_exdentes,$rows_indebidos,$rows_gar,$rows_gar_noreg;
            
                            $sheet->fromModel($rows_indebidos,null, 'A1', false, false);
                            $sheet->cells('A1:C1', function($cells) {
                            // manipulate the range of cells
                            $cells->setBackground('#058A37');
                            $cells->setFontColor('#ffffff');  
                            $cells->setFontWeight('bold');
                            });
                        });
                    $excel->sheet('prestamos no reg',function($sheet) {
                        global $rows_exacta,$rows_not_found,$rows_noreg,$rows_exdentes,$rows_indebidos,$rows_gar,$rows_gar_noreg,$rows_pres_noreg;
            
                            $sheet->fromModel($rows_pres_noreg,null, 'A1', false, false);
                            $sheet->cells('A1:C1', function($cells) {
                            // manipulate the range of cells
                            $cells->setBackground('#058A37');
                            $cells->setFontColor('#ffffff');  
                            $cells->setFontWeight('bold');
                            });
                        });
                    $excel->sheet('exdentes reg',function($sheet) {
                        global $rows_exacta,$rows_not_found,$rows_noreg,$rows_exdentes,$rows_indebidos,$rows_gar,$rows_gar_noreg,$rows_pres_noreg;
            
                            $sheet->fromModel($rows_exdentes,null, 'A1', false, false);
                            $sheet->cells('A1:C1', function($cells) {
                            // manipulate the range of cells
                            $cells->setBackground('#058A37');
                            $cells->setFontColor('#ffffff');  
                            $cells->setFontWeight('bold');
                            });
                        });
                    
                    $excel->sheet('no encontrados',function($sheet) {
                        global $rows_exacta,$rows_not_found,$rows_noreg,$rows_exdentes,$rows_indebidos,$rows_gar,$rows_gar_noreg;
            
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
