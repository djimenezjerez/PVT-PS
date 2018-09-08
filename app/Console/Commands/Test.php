<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use DB;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'keyrus:test';

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
        global $rows,$rows_not_found;
        
        $this->info('generando prestamos');
        //  $areas = DB::table('Area')->get();
        //$this->info(var_dump($areas));

        $loans = DB::table('Prestamos')->where('PresEstPtmo','=','V')->get();

        $bar = $this->output->createProgressBar(count($loans));
        global $rows_exacta;
        $rows_exacta = array();
        array_push($rows_exacta,array('Prestamos.IdPrestamo',' Prestamos.PresNumero','PresFechaDesembolso','Prestamos.PresCuotaMensual'));
        foreach($loans as $loan)
        {
            $amortizaciones = DB::table('Amortizacion')->where('IdPrestamo','=',$loan->IdPrestamo)->whereRaw("AmrSts!='X' and YEAR(AmrFecPag)=2018")->get();
            if(sizeof($amortizaciones)>0)
            {
                $saldo_anterior = $loan->PresMntDesembolso;
                // $saldo_actual = $amortizacion[0]->AmrSldAct;
                $sw = false;
                foreach($amortizaciones as $amortizacion)
                {
                    if($amortizacion->AmrSldAct>$saldo_anterior)
                    {
                        $sw = true;
                    }
                    $saldo_anterior = $amortizacion->AmrSldAct;
                }
                if($sw)
                {
                    array_push($rows_exacta,array($loan->IdPrestamo,$loan->PresNumero,$loan->PresFechaDesembolso,$loan->PresCuotaMensual));
                }

            }
            $bar->advance();
            
            // array_push($rows_exacta,array($loan->IdPrestamo,$loan->PresNumero,$loan->PresFechaDesembolso,$loan->PresCuotaMensual));
        }

        Excel::create('prestamos irregulares',function($excel)
        {
            global $rows_exacta,$rows_not_found,$rows_desc_mayor,$rows_desc_menor,$rows_segundo_prestamo,$prestamos_noreg,$rows_gar;
            
                $excel->sheet('prestamos',function($sheet) {
                    global $rows_exacta,$rows_not_found,$rows_desc_mayor,$rows_desc_menor;
    
                    $sheet->fromModel($rows_exacta,null, 'A1', false, false);
                    $sheet->cells('A1:C1', function($cells) {
                    // manipulate the range of cells
                    $cells->setBackground('#058A37');
                    $cells->setFontColor('#ffffff');  
                    $cells->setFontWeight('bold');
                    });
                });
                  
        })->store('xls', storage_path('excel/export'));
        $bar->finish();
        // })->store('xls', storage_path());
        $this->info('Finished');
    }
}
