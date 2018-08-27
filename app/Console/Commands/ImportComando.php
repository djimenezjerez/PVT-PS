<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use DB;

class ImportComando extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prestamos:ImportComando';

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
        global $rows_exacta,$rows_not_found,$rows_desc_mayor,$rows_desc_menor;

        $this->info('Iniciando Modulo de Importacion');
        $path = storage_path('excel/import/MUSERPOL JULIO 2018.xls');
        Excel::selectSheetsByIndex(0)->load($path, function($reader) {
            
            global $rows_exacta,$rows_not_found,$rows_desc_mayor,$rows_desc_menor;

            $rows_exacta = Array();
            $rows_not_found = Array();
            $rows_desc_mayor = Array();
            $rows_desc_menor = Array();
            
            array_push($rows_not_found,array('nit','ci','app','apm', 'nom1', 'nom2', 'desc_mes'));
            
            array_push($rows_exacta,array('Prestamos.IdPrestamo',' Prestamos.PresNumero','PresFechaDesembolso','Producto','Prestamos.PresCuotaMensual','Amortizacion.AmrFecPag','Amortizacion.AmrFecTrn','capital','Interes','Interes penal','otros cobros','Amortizacion.AmrNroCpte','Tipo pago','Amortizacion.AmrTotPag','Descuento_comando','Padron.PadMatricula',' Padron.PadCedulaIdentidad','Padron.PadMaterno',' Padron.PadPaterno',' Padron.PadNombres','Padron.PadNombres2do','Padron.Tipo'));
            array_push($rows_desc_mayor,array('Prestamos.IdPrestamo',' Prestamos.PresNumero','PresFechaDesembolso','Producto','Prestamos.PresCuotaMensual','Amortizacion.AmrFecPag','Amortizacion.AmrFecTrn','capital','Interes','Interes penal','otros cobros','Amortizacion.AmrNroCpte','Tipo pago','Amortizacion.AmrTotPag','Descuento_comando','Padron.PadMatricula',' Padron.PadCedulaIdentidad','Padron.PadMaterno',' Padron.PadPaterno',' Padron.PadNombres','Padron.PadNombres2do','Padron.Tipo'));
            array_push($rows_desc_menor,array('Prestamos.IdPrestamo',' Prestamos.PresNumero','PresFechaDesembolso','Producto','Prestamos.PresCuotaMensual','Amortizacion.AmrFecPag','Amortizacion.AmrFecTrn','capital','Interes','Interes penal','otros cobros','Amortizacion.AmrNroCpte','Tipo pago','Amortizacion.AmrTotPag','Descuento_comando','Padron.PadMatricula',' Padron.PadCedulaIdentidad','Padron.PadMaterno',' Padron.PadPaterno',' Padron.PadNombres','Padron.PadNombres2do','Padron.Tipo'));

        

            $result = $reader->select(array('nit','ci','app','apm', 'nom1', 'nom2', 'desc_mes'))
             //->take(200)
             ->get();
            // reader methods
            foreach($result as $row){
                $ci = (int)trim($row->ci);
                $descuento = floatval(str_replace(",","",$row->desc_mes));
                $this->info($ci);
                $amortizacion = DB::table('Amortizacion')
                                ->join('Prestamos','Prestamos.IdPrestamo','=','Amortizacion.IdPrestamo')
                                ->join('Padron','Padron.IdPadron','=','Prestamos.IdPadron')
                                ->join('Producto','Producto.PrdCod','=','Prestamos.PrdCod')
                                ->where('Padron.PadTipo','=','ACTIVO')
                                ->where('Prestamos.PresEstPtmo','=','V')
                                ->where('Amortizacion.AmrNroCpte','=','D-07/18')
                                ->where('Amortizacion.AmrSts','!=','X')
                                ->where('Padron.PadMatricula','=',''.$ci)
                                //->select('Prestamos.IdPrestamo',' Prestamos.PresNumero','Prestamos.PresCuotaMensual','Amortizacion.AmrFecPag','Amortizacion.AmrFecTrn','Amortizacion.AmrNroCpte','Amortizacion.AmrTotPag','Padron.PadMatricula',' Padron.PadCedulaIdentidad','Padron.PadMaterno',' Padron.PadPaterno',' Padron.PadNombres','Padron.PadNombres2do')
                                ->first();
                if($amortizacion)
                {
                    if( $amortizacion->AmrTotPag == $descuento)
                    {
                        $this->info($amortizacion->AmrTotPag.' exacto '.$descuento);
                        array_push($rows_exacta,array($amortizacion->IdPrestamo,$amortizacion->PresNumero,$amortizacion->PresFechaDesembolso,$amortizacion->PrdDsc,$amortizacion->PresCuotaMensual,$amortizacion->AmrFecPag,$amortizacion->AmrFecTrn,$amortizacion->AmrCap,$amortizacion->AmrInt,$amortizacion->AmrIntPen,$amortizacion->AmrOtrCob,$amortizacion->AmrNroCpte,$amortizacion->AmrTipPAgo,$amortizacion->AmrTotPag,$descuento,$amortizacion->PadMatricula,$amortizacion->PadCedulaIdentidad,utf8_encode($amortizacion->PadMaterno),utf8_encode($amortizacion->PadPaterno),utf8_encode($amortizacion->PadNombres),utf8_encode($amortizacion->PadNombres2do),$amortizacion->PadTipo));
                    }
                    else
                    {
                        if($amortizacion->AmrTotPag > $descuento) //posible pago a garante
                        {
                            $this->info($amortizacion->AmrTotPag.' garhdp '.$descuento);
                            array_push($rows_desc_mayor,array($amortizacion->IdPrestamo,$amortizacion->PresNumero,$amortizacion->PresFechaDesembolso,$amortizacion->PrdDsc,$amortizacion->PresCuotaMensual,$amortizacion->AmrFecPag,$amortizacion->AmrFecTrn,$amortizacion->AmrCap,$amortizacion->AmrInt,$amortizacion->AmrIntPen,$amortizacion->AmrOtrCob,$amortizacion->AmrNroCpte,$amortizacion->AmrTipPAgo,$amortizacion->AmrTotPag,$descuento,$amortizacion->PadMatricula,$amortizacion->PadCedulaIdentidad,utf8_encode($amortizacion->PadMaterno),utf8_encode($amortizacion->PadPaterno),utf8_encode($amortizacion->PadNombres),utf8_encode($amortizacion->PadNombres2do),$amortizacion->PadTipo));
                        }
                        else
                        {
                            $this->info($amortizacion->AmrTotPag.' mora_parcial '.$descuento);
                            array_push($rows_desc_menor,array($amortizacion->IdPrestamo,$amortizacion->PresNumero,$amortizacion->PresFechaDesembolso,$amortizacion->PrdDsc,$amortizacion->PresCuotaMensual,$amortizacion->AmrFecPag,$amortizacion->AmrFecTrn,$amortizacion->AmrCap,$amortizacion->AmrInt,$amortizacion->AmrIntPen,$amortizacion->AmrOtrCob,$amortizacion->AmrNroCpte,$amortizacion->AmrTipPAgo,$amortizacion->AmrTotPag,$descuento,$amortizacion->PadMatricula,$amortizacion->PadCedulaIdentidad,utf8_encode($amortizacion->PadMaterno),utf8_encode($amortizacion->PadPaterno),utf8_encode($amortizacion->PadNombres),utf8_encode($amortizacion->PadNombres2do),$amortizacion->PadTipo));
                        }
                        
                    }
                }
                else{

                    $this->info($ci." not found");
                    array_push($rows_not_found,array($row->nit,$row->ci,$row->app,$row->apm,$row->nom1,$row->nom2,$row->desc_mes));
                }
                $amortizacion = null;

            }
            
            $this->info('total rows'.$result->count());

        });

        Excel::create('Sismu registrados',function($excel)
        {
            global $rows_exacta,$rows_not_found,$rows_desc_mayor,$rows_desc_menor;
            
                    $excel->sheet('Descuentos exactos',function($sheet) {
                            global $rows_exacta,$rows_not_found,$rows_desc_mayor,$rows_desc_menor;
            
                            $sheet->fromModel($rows_exacta,null, 'A1', false, false);
                            $sheet->cells('A1:C1', function($cells) {
                            // manipulate the range of cells
                            $cells->setBackground('#058A37');
                            $cells->setFontColor('#ffffff');  
                            $cells->setFontWeight('bold');
                            });
                        });
                    $excel->sheet('descuento mayor',function($sheet) {
                            global $rows_exacta,$rows_not_found,$rows_desc_mayor,$rows_desc_menor;
            
                            $sheet->fromModel($rows_desc_mayor,null, 'A1', false, false);
                            $sheet->cells('A1:C1', function($cells) {
                            // manipulate the range of cells
                            $cells->setBackground('#058A37');
                            $cells->setFontColor('#ffffff');  
                            $cells->setFontWeight('bold');
                            });
                        });
                    $excel->sheet('descuento menor',function($sheet) {
                            global $rows_exacta,$rows_not_found,$rows_desc_mayor,$rows_desc_menor;
            
                            $sheet->fromModel($rows_desc_menor,null, 'A1', false, false);
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
