<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use DB;
class ImportSaldosAnteriores extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tesoreria:ImportSaldosAnteriores';

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
        $path = storage_path('excel/import/resagados.xls');
        $this->info("importando: ".$path);
        Excel::selectSheetsByIndex(0)->load($path , function($reader) {
            
            // reader methods
            $result = $reader->select(array('fecha','comprobante','cod_sigep','nro_doc','beneficiario', 'monto'))
                                //  ->take(100)
                             ->get();
            $this->info('total rows:'.$result->count());
            $bar = $this->output->createProgressBar(count($result));
            foreach($result as $row){

                // $this->info($row->monto<0?$row->monto*-1:$row->monto);
                // $array_fecha =explode('/',$row->fecha);
                $bar->advance();
                DB::table('saldos_anteriores')
                ->insert(['fecha'=> $row->fecha,
                          'comprobante'=> trim($row->comprobante),
                          'cod_sigep'=> trim($row->cod_sigep),
                          'nro_doc'=> trim($row->nro_doc),
                          'beneficiario'=> trim($row->beneficiario),
                          'monto'=> $row->monto,
                          ]);
            }
            $bar->finish();
        });
    }
}
