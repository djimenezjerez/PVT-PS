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
        $path = storage_path('excel/export/Sismu registrados.xls');
        Excel::selectSheetsByIndex(3)->load($path, function($reader) {
            
            global $rows_exacta,$rows_not_found,$rows_desc_mayor,$rows_desc_menor,$rows_indebidos;

            $rows_exacta = Array();
            $rows_not_found = Array();
            $rows_desc_mayor = Array();
            $rows_desc_menor = Array();
            $rows_indebidos = Array();
            
            array_push($rows_not_found,array('nit','ci','app','apm', 'nom1', 'nom2', 'desc_mes'));
            array_push($rows_indebidos,array('nit','ci','app','apm', 'nom1', 'nom2', 'desc_mes'));
            
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

                $padron = DB::table('Padron')
                              ->where('PadMatricula','=',''.$ci)
                              ->first();
                if($padron)
                {
                    $this->info($padron->PadCedulaIdentidad.' found');

                    // buscando prestamos
                    $prestamos = DB::table('Prestamos')
                                    ->where('Prestamos.IdPadron','=',$padron->IdPadron)
                                    ->where('PresEstPtmo','=','V')
                                    ->get();
                    if($prestamos)
                    {
                        //verificar los siguientes casos : 1 prestamo 1 garante, 2 prestamos, 2 prestamos 1 garante, 2 prestamos 2 garantes XD
                        if($prestamos->count()>1)
                        {

                        }else{

                        }

                    }else{ //solo es garante
                        //sigla

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

                        //verificar a quien esta garantizando XD 
                        $garantizos = DB::table('PrestamosLevel1')
                                    ->where('IdPadronGar','=',$padron->IdPadron)
                                    ->get();
                        if($garantizos)
                        {
                            $prestamos_vigentes = array();
                            $descuento_prestamos = 0;
                            foreach($garantizos as $garantizo){
                                $prestamo = DB::table('Prestamos')->where('IdPrestamo',$garantizo->IdPrestamo)->where('PresEstPtmo','=','V')->first();
                                if($prestamo)
                                {
                                    $descuento_prestamos += $prestamo->PresCuotaMensual;
                                    array_push($prestamos_vigentes,$prestamo);
                                }
                            }
                            // if($descuento == $descuento_prestamos) // el policia esta pagando como garante de 1 prestamo o de 2 prestamos exactos
                            // {
                                foreach($prestamos_vigentes as $prestamo_vigente){
                                   
                                    $amortizacion = DB::table('Amortizacion')
                                                        ->join('Prestamos','Prestamos.IdPrestamo','=','Amortizacion.IdPrestamo')
                                                        ->join('Padron','Padron.IdPadron','=','Prestamos.IdPadron')
                                                        ->join('Producto','Producto.PrdCod','=','Prestamos.PrdCod')
                                                        ->where('Padron.PadTipo','=','ACTIVO')
                                                        ->where('Prestamos.IdPrestamo','=',$prestamo_vigente->IdPrestamo)
                                                        ->where('Amortizacion.AmrNroCpte','=',$sigla)
                                                        ->where('Amortizacion.AmrSts','!=','X')
                                                        ->where('Padron.IdPadron','=',$padron->IdPadron)
                                                        ->first();
                                    if($amortizacion)
                                    {
                                        if($descuento==$amortizacion->AmrTotPag)
                                        {
                                            //conciliado mas
                                        }
                                        else{
                                            // esta pagando mas de un garante --- registrar el prestamo

                                        }
                                    }
                                 }
                            // }
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
            $this->info('total :( '.sizeOf($rows_not_found));
            $this->info('total :v '.sizeOf($rows_indebidos));
        });
    }
}
