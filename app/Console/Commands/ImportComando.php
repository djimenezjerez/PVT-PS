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
        $this->info('Iniciando Modulo de Importacion');
        $path = storage_path('desc_muserpol_JUNIO2018.xls');
        Excel::selectSheetsByIndex(0)->load($path, function($reader) {
            
            $result = $reader->select(array('nit','ci','app','apm', 'nom1', 'nom2', 'desc_mes'))
             //->take(100)
             ->get();
            // reader methods
            foreach($result as $row){
                $this->info($row);
                // $this->info(floatval(str_replace(",","",$row->desc_mes)));

                DB::table('afiliados_comando')
                ->insert(['fecha'=>'2018-06-01',
                          'nit'=> trim($row->nit),
                          'ci'=> (int) trim($row->ci),
                          'paterno'=> trim($row->app),
                          'materno'=> trim($row->apm),
                          'primer_nombre'=> trim($row->nom1),
                          'segundo_nombre'=> trim($row->nom2),
                          'descuento'=> floatval(str_replace(",","",$row->desc_mes)),
                          'tipo'=> ''
                          ]);
            }
            
            $this->info('total rows'.$result->count());
        });
        
    }
}
