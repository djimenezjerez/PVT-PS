<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use DB;

class ImportSenasir extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prestamos:ImportSenasir';

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
        // $this->info('Iniciando Modulo de Importacion');
        $path = storage_path('Senasir.xlsx');
        Excel::selectSheetsByIndex(0)->load($path , function($reader) {
            
            // reader methods
            $result = $reader->select(array('regional','aseguradora','matricula','matricula_dh', 'paterno', 'materno', 'nombres', 'descuento'))
            // ->take(100)
             ->get();
            foreach($result as $row){
                $this->info($row);
                DB::table('afiliados_senasir')
                ->insert(['fecha'=>'2018-06-01',
                          'regional'=> trim($row->regional),
                          'aseguradora'=> trim($row->aseguradora),
                          'matricula'=> trim($row->matricula),
                          'matricula_dh'=> trim($row->matricula_dh),
                          'paterno'=> trim($row->paterno),
                          'materno'=> trim($row->materno),
                          'nombres'=> trim($row->nombres),
                          'descuento'=> trim($row->descuento)
                          ]);

            }
            $this->info('total rows:'.$result->count());
        });
    }
}
