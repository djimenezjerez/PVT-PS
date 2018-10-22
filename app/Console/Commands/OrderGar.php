<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use DB;

class OrderGar extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prestamos:OrderGar';

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
        $excel = 'true';
        $date = '01/10/2018';
        // Log::info($excel);
        $loans = DB::select("SELECT dbo.Prestamos.IdPrestamo,dbo.Prestamos.PresSaldoAct,dbo.Prestamos.PresCuotaMensual,dbo.Prestamos.PresFechaDesembolso,Producto.PrdDsc,dbo.Prestamos.PresNumero,dbo.Padron.IdPadron, DATEDIFF(month, Amortizacion.AmrFecPag, '2018-09-30') as meses_mora from dbo.Prestamos
        join dbo.Padron on Prestamos.IdPadron = Padron.IdPadron
        join dbo.Producto on Prestamos.PrdCod = Producto.PrdCod
        join dbo.Amortizacion on (Prestamos.IdPrestamo = Amortizacion.IdPrestamo and Amortizacion.AmrNroPag = (select max(AmrNroPag) from Amortizacion where Amortizacion.IdPrestamo = Prestamos.IdPrestamo AND Amortizacion.AMRSTS <>'X' ))
        where Prestamos.PresEstPtmo = 'V' and dbo.Prestamos.PresSaldoAct > 0 and Amortizacion.AmrFecPag <  cast('2018-09-30' as datetime)
        order by meses_mora DESC;");
        // $loans = DB::select("SELECT dbo.Prestamos.IdPrestamo,dbo.Prestamos.PresSaldoAct,dbo.Prestamos.PresCuotaMensual,dbo.Prestamos.PresFechaDesembolso,Producto.PrdDsc,dbo.Prestamos.PresNumero,dbo.Padron.IdPadron from dbo.Prestamos
        // join dbo.Padron on Prestamos.IdPadron = Padron.IdPadron
        // join dbo.Producto on Prestamos.PrdCod = Producto.PrdCod
        // join dbo.Amortizacion on (Prestamos.IdPrestamo = Amortizacion.IdPrestamo and Amortizacion.AmrNroPag = (select max(AmrNroPag) from Amortizacion where Amortizacion.IdPrestamo = Prestamos.IdPrestamo AND Amortizacion.AMRSTS <>'X' ))
        // where Prestamos.PresEstPtmo = 'V' and dbo.Prestamos.PresSaldoAct > 0 and Amortizacion.AmrFecPag <  cast('2018-08-31' as datetime) ;");
        $this->info(sizeof($loans));
        
        $prestamos= [];
        global $rows_exacta;
        $rows_exacta = Array();
        array_push($rows_exacta,array('Prestamos.PresNumero','PresFechaDesembolso','Cutoa Mensual','Saldo Actual','Tipo','Producto','Padron.PadMatricula',' Padron.PadCedulaIdentidad',' Padron.PadPaterno','Padron.PadMaterno',' Padron.PadNombres','Padron.PadNombres2do','Meses Mora','matricula','ci','ext','nom1','nom2','paterno','materno','tipo'));
        foreach($loans as $loan)
        {
            $padron = DB::table('Padron')->where('IdPadron',$loan->IdPadron)->first();
            $loan->PadTipo = utf8_encode(trim($padron->PadTipo));
            $loan->PadNombres = utf8_encode(trim($padron->PadNombres));
            $loan->PadNombres2do =utf8_encode(trim($padron->PadNombres2do));
            $loan->PadPaterno =utf8_encode(trim($padron->PadPaterno));
            $loan->PadMaterno =utf8_encode(trim($padron->PadMaterno));
            $loan->PadCedulaIdentidad =utf8_encode(trim($padron->PadCedulaIdentidad));
            $loan->PadExpCedula =utf8_encode(trim($padron->PadExpCedula));
            $loan->PadMatricula =utf8_encode(trim($padron->PadMatricula));
            
            
            
            
            if($excel!='')//reporte excel hdp 
            {
                $row = array($loan->PresNumero,$loan->PresFechaDesembolso,$loan->PresCuotaMensual,$loan->PresSaldoAct,$loan->PadTipo,$loan->PrdDsc,$loan->PadMatricula,$loan->PadCedulaIdentidad,$loan->PadPaterno,$loan->PadMaterno,$loan->PadNombres,$loan->PadNombres2do,$loan->meses_mora);
                
                $garantes = DB::table('PrestamosLevel1')->where('IdPrestamo','=',$loan->IdPrestamo)->get();
                if(sizeof($garantes)>0)
                {
                    foreach($garantes as $garante)
                    {
                        $padron_gar = DB::table('Padron')->where('Padron.IdPadron','=',$garante->IdPadronGar)->first();
                        array_push($row,utf8_encode(trim($padron_gar->PadMatricula)),utf8_encode(trim($padron_gar->PadCedulaIdentidad)),utf8_encode(trim($padron_gar->PadExpCedula)),utf8_encode(trim($padron_gar->PadNombres)),utf8_encode(trim($padron_gar->PadNombres2do)),utf8_encode(trim($padron_gar->PadPaterno)),utf8_encode(trim($padron_gar->PadMaterno)),utf8_encode(trim($padron_gar->PadTipo)),'*');

                    }
                }
                array_push($rows_exacta,$row);    
            }else{
                array_push($prestamos,$loan);
            }
        }
       
        Excel::create('prestamos en mora',function($excel)
        {
            global $rows_exacta;
            
                    $excel->sheet('mora_parcial',function($sheet) {
                            global $rows_exacta;
            
                            $sheet->fromModel($rows_exacta,null, 'A1', false, false);
                            $sheet->cells('A1:M1', function($cells) {
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
