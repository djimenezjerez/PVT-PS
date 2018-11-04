<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use DB;
class ImportExtractoBanco extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tesoreria:ImportExtractoBanco';

    /**
     * The console command descriptio
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
        $path = storage_path('excel/import/extracto cta 1.xls');
        $this->info("importando: ".$path);
        Excel::selectSheetsByIndex(0)->load($path , function($reader) {
            
            // reader methods
            $result = $reader->select(array('fecha','agencia','descripcion','nro_doc', 'monto'))
                                //  ->take(100)
                             ->get();
            $this->info('total rows:'.$result->count());
            $bar = $this->output->createProgressBar(count($result));
            foreach($result as $row){

                // $this->info($row->monto<0?$row->monto*-1:$row->monto);
                // $array_fecha =explode('/',$row->fecha);
                $bar->advance();
                DB::table('extracto_bancario')
                ->insert(['fecha'=> $row->fecha,
                          'agencia'=> trim($row->agencia),
                          'descripcion'=> trim($row->descripcion),
                          'nro_doc'=> trim($row->nro_doc),
                          'monto'=> $row->monto<0?$row->monto*-1:$row->monto,
                          ]);
            }
            $bar->finish();
        });
    }
}
