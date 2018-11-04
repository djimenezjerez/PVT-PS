<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use DB;

class ImportSigepMayor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tesoreria:ImportSigep';

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
        $path = storage_path('excel/import/mayor.xls');
        $this->info("importando: ".$path);
        Excel::selectSheetsByIndex(0)->load($path , function($reader) {
            
            // reader methods
            $result = $reader->select(array('fecha','comprobante','identificador','cod_sigep', 'monto', 'debe', 'haber', 'linea' ,'beneficiario','nro_doc'))
                                // ->take(100)
                             ->get();
            $this->info('total rows:'.$result->count());
            $bar = $this->output->createProgressBar(count($result));
            foreach($result as $row){
                // $this->info($row->monto);
                // $array_fecha =explode('/',$row->fecha);
                $bar->advance();
                DB::table('mayores_sigep')
                ->insert(['fecha'=> $row->fecha,
                          'comprobante'=> trim($row->comprobante),
                          'identificador'=> trim($row->identificador),
                          'cod_sigep'=> trim($row->cod_sigep),
                          'monto'=> $row->monto,
                          'debe'=> $row->debe,
                          'haber'=> $row->haber,
                          'haber'=> $row->haber,
                          'linea'=> $row->linea,
                          'beneficiario'=> trim($row->beneficiario),
                          'nro_doc'=> trim($row->nro_doc),
                          ]);
            }
            $bar->finish();
        });
    }
}
