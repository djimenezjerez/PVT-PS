<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use DB;
use Log;
class CobranzasComprobantes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prestamos:CobranzasComprobantes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Paso 4: Buscar en funcion de otro comprobantes XD';

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
        $this->info("Consiliador Beta 0.1");
        global $rows_exacta,$rows_not_found,$rows_dos_exacto,$rows_verificar,$nro_comprobante,$prestamos_noreg,$rows_gar;
        $nro_comprobante = $this->ask('Ingrese el numero de comprobante?');

        $this->info('Iniciando Modulo de Importacion');
        $path = storage_path('excel/export/Prestamos Pendientes c3.xls');
        Excel::selectSheetsByIndex(2)->load($path, function($reader) {
            
            global $rows_exacta,$rows_not_found,$rows_dos_exacto,$rows_verificar,$nro_comprobante,$prestamos_noreg,$rows_gar;

            $rows_exacta = Array();
           
            $rows_not_found = Array();
            $rows_dos_exacto = Array();
            $rows_verificar = Array();
            $rows_gar = Array();
            $prestamos_noreg = Array();
            
            array_push($rows_not_found,array('ci','app','apm', 'nom1', 'nom2', 'desc_mes'));
            
            array_push($rows_exacta,array('Prestamos.PresNumero','PresFechaDesembolso','Tipo','Fecha Pago','Fecha Transaccion','Producto','Padron.PadMatricula',' Padron.PadCedulaIdentidad',' Padron.PadPaterno','Padron.PadMaterno',' Padron.PadNombres','Padron.PadNombres2do', 'Capital','Interes','Interes penal','otros cobros','Amortizacion.AmrTotPag','Tipo Descuento','Numero Comprobante','*','ci','app','apm', 'nom1', 'nom2', 'desc_mes'));
            array_push($rows_dos_exacto,array('Prestamos.PresNumero','PresFechaDesembolso','Tipo','Fecha Pago','Fecha Transaccion','Producto','Padron.PadMatricula',' Padron.PadCedulaIdentidad',' Padron.PadPaterno','Padron.PadMaterno',' Padron.PadNombres','Padron.PadNombres2do', 'Capital','Interes','Interes penal','otros cobros','Amortizacion.AmrTotPag','Tipo Descuento','Numero Comprobante','*','ci','app','apm', 'nom1', 'nom2', 'desc_mes'));

            $result = $reader->select(array('ci','app','apm', 'nom1', 'nom2', 'desc_mes'))
             //->take(500)
             ->get();
            // reader methods
            $bar = $this->output->createProgressBar(count($result));


            foreach($result as $row){
                $ci = trim($row->ci);
                $descuento = floatval($row->desc_mes);
                //$this->info($ci);
                //Log::info($ci.' '.$descuento);
                $amortizaciones = DB::table('Amortizacion')
                                ->join('Prestamos','Prestamos.IdPrestamo','=','Amortizacion.IdPrestamo')
                                ->join('Padron','Padron.IdPadron','=','Prestamos.IdPadron')
                                ->join('Producto','Producto.PrdCod','=','Prestamos.PrdCod')
                                ->where('Padron.PadTipo','=','ACTIVO')
                                ->where('Prestamos.PresEstPtmo','=','V')
                                ->where('Amortizacion.AmrNroCpte','=',$nro_comprobante)
                                ->where('Amortizacion.AmrSts','!=','X')
                                ->where('Padron.PadCedulaIdentidad','=',''.$ci)
                                ->select('Prestamos.PresNumero','Prestamos.PresFechaDesembolso',
                                         'Producto.PrdDsc',
                                         'Amortizacion.AmrFecPag', 'Amortizacion.AmrFecTrn','Amortizacion.AmrCap','Amortizacion.AmrInt','Amortizacion.AmrIntPen','Amortizacion.AmrOtrCob','Amortizacion.AmrTotPag','Amortizacion.AmrTipPAgo' ,'Amortizacion.AmrNroCpte',
                                         'Padron.IdPadron')
                                ->get();

              

                if(sizeof($amortizaciones)>0)
                {
                    
                    if(sizeof($amortizaciones)==1) //1 descuento y 1 prestamo
                    {
                        foreach($amortizaciones as $amortizacion)
                        {
                            if( $amortizacion->AmrTotPag == $descuento)
                            {
                                $padron = DB::table('Padron')->where('IdPadron',$amortizacion->IdPadron)->first();
                                $amortizacion->PadTipo = utf8_encode(trim($padron->PadTipo));
                                $amortizacion->PadNombres = utf8_encode(trim($padron->PadNombres));
                                $amortizacion->PadNombres2do =utf8_encode(trim($padron->PadNombres2do));
                                $amortizacion->PadPaterno =utf8_encode(trim($padron->PadPaterno));
                                $amortizacion->PadMaterno =utf8_encode(trim($padron->PadMaterno));
                                $amortizacion->PadCedulaIdentidad =utf8_encode(trim($padron->PadCedulaIdentidad));
                                $amortizacion->PadExpCedula =utf8_encode(trim($padron->PadExpCedula));
                                $amortizacion->PadMatricula =utf8_encode(trim($padron->PadMatricula));

                                array_push($rows_exacta,array($amortizacion->PresNumero,$amortizacion->PresFechaDesembolso,$amortizacion->PadTipo,$amortizacion->AmrFecPag,$amortizacion->AmrFecTrn,$amortizacion->PrdDsc,$amortizacion->PadMatricula,$amortizacion->PadCedulaIdentidad,utf8_encode($amortizacion->PadPaterno),utf8_encode($amortizacion->PadMaterno),utf8_encode($amortizacion->PadNombres),utf8_encode($amortizacion->PadNombres2do), $amortizacion->AmrCap,$amortizacion->AmrInt,$amortizacion->AmrIntPen,$amortizacion->AmrOtrCob,$amortizacion->AmrTotPag,$amortizacion->AmrTipPAgo,$amortizacion->AmrNroCpte,'*',$row->ci,$row->app,$row->apm,$row->nom1,$row->nom2,$row->desc_mes));
                            }else
                            {
                                array_push($rows_not_found,array($row->ci,$row->app,$row->apm,$row->nom1,$row->nom2,$row->desc_mes));
                            }
                        }
                    }
                    
                    if(sizeof($amortizaciones)==2) //1 descuento y 2 prestamos
                    {
                        $descuento_total= 0;
                        
                        foreach($amortizaciones as $amortizacion)
                        {
                            $descuento_total += $amortizacion->AmrTotPag;
                        }

                        if($descuento_total == $descuento)
                        {
                            $print=true;
                            foreach($amortizaciones as $amortizacion)
                            {
                                $padron = DB::table('Padron')->where('IdPadron',$amortizacion->IdPadron)->first();
                                $amortizacion->PadTipo = utf8_encode(trim($padron->PadTipo));
                                $amortizacion->PadNombres = utf8_encode(trim($padron->PadNombres));
                                $amortizacion->PadNombres2do =utf8_encode(trim($padron->PadNombres2do));
                                $amortizacion->PadPaterno =utf8_encode(trim($padron->PadPaterno));
                                $amortizacion->PadMaterno =utf8_encode(trim($padron->PadMaterno));
                                $amortizacion->PadCedulaIdentidad =utf8_encode(trim($padron->PadCedulaIdentidad));
                                $amortizacion->PadExpCedula =utf8_encode(trim($padron->PadExpCedula));
                                $amortizacion->PadMatricula =utf8_encode(trim($padron->PadMatricula));
                                if($print)
                                {
                                    $print=false;
                                    array_push($rows_dos_exacto,array($amortizacion->PresNumero,$amortizacion->PresFechaDesembolso,$amortizacion->PadTipo,$amortizacion->AmrFecPag,$amortizacion->AmrFecTrn,$amortizacion->PrdDsc,$amortizacion->PadMatricula,$amortizacion->PadCedulaIdentidad,utf8_encode($amortizacion->PadPaterno),utf8_encode($amortizacion->PadMaterno),utf8_encode($amortizacion->PadNombres),utf8_encode($amortizacion->PadNombres2do), $amortizacion->AmrCap,$amortizacion->AmrInt,$amortizacion->AmrIntPen,$amortizacion->AmrOtrCob,$amortizacion->AmrTotPag,$amortizacion->AmrTipPAgo,$amortizacion->AmrNroCpte,'*',$row->ci,$row->app,$row->apm,$row->nom1,$row->nom2,$row->desc_mes));
                                }else{
                                    array_push($rows_dos_exacto,array($amortizacion->PresNumero,$amortizacion->PresFechaDesembolso,$amortizacion->PadTipo,$amortizacion->AmrFecPag,$amortizacion->AmrFecTrn,$amortizacion->PrdDsc,$amortizacion->PadMatricula,$amortizacion->PadCedulaIdentidad,utf8_encode($amortizacion->PadPaterno),utf8_encode($amortizacion->PadMaterno),utf8_encode($amortizacion->PadNombres),utf8_encode($amortizacion->PadNombres2do), $amortizacion->AmrCap,$amortizacion->AmrInt,$amortizacion->AmrIntPen,$amortizacion->AmrOtrCob,$amortizacion->AmrTotPag,$amortizacion->AmrTipPAgo,$amortizacion->AmrNroCpte,'*',$row->ci,$row->app,$row->apm,$row->nom1,$row->nom2));
                                }
                            }
                        }
                        else {
                                array_push($rows_not_found,array($row->ci,$row->app,$row->apm,$row->nom1,$row->nom2,$row->desc_mes));
                        }

                    }

                    if(sizeof($amortizaciones)>2) // esto no deberia pasar  
                    {
                        array_push($rows_verificar,$row->ci);
                        array_push($rows_not_found,array($row->ci,$row->app,$row->apm,$row->nom1,$row->nom2,$row->desc_mes));
                    }
                   
                }
                else{

                    //$this->info($ci." not found");
                    array_push($rows_not_found,array($row->ci,$row->app,$row->apm,$row->nom1,$row->nom2,$row->desc_mes));
                }
                $amortizacion = null;
                $bar->advance();
            }
            $bar->finish();
            $this->info('total rows'.$result->count());
            
        });

        $this->info("raros: ".sizeof($rows_verificar));
        foreach($rows_verificar as $item)
        {
            $this->info($item);
        }

        Excel::create('Prestamos comprbante diferente c4',function($excel)
        {
            global $rows_exacta,$rows_not_found,$rows_dos_exacto,$rows_verificar,$nro_comprobante,$prestamos_noreg,$rows_gar;
            
                    $excel->sheet('Descuentos exactos',function($sheet) {
                            global $rows_exacta,$rows_not_found,$rows_dos_exacto,$rows_verificar;
            
                            $sheet->fromModel($rows_exacta,null, 'A1', false, false);
                            $sheet->cells('A1:C1', function($cells) {
                            // manipulate the range of cells
                            $cells->setBackground('#058A37');
                            $cells->setFontColor('#ffffff');  
                            $cells->setFontWeight('bold');
                            });
                        });
                    $excel->sheet('Dobles exactos',function($sheet) {
                            global $rows_exacta,$rows_not_found,$rows_dos_exacto,$rows_verificar;
            
                            $sheet->fromModel($rows_dos_exacto,null, 'A1', false, false);
                            $sheet->cells('A1:C1', function($cells) {
                            // manipulate the range of cells
                            $cells->setBackground('#058A37');
                            $cells->setFontColor('#ffffff');  
                            $cells->setFontWeight('bold');
                            });
                        });
                  
                 
                    $excel->sheet('Sobrante',function($sheet) {
                            global $rows_exacta,$rows_not_found,$rows_dos_exacto,$rows_verificar;
            
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
