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
        $this->info("testeando db");
        // $query = DB::table('Area')->get();
        // $colect = json_encode($query);
        // foreach($query as $r ){
        //     $this->info(json_encode($r));

        // }
        // $this->info(json_decode($colect));
        $this->info("Ordenando XD");
        $path = storage_path('AmortizacionActivos (1).xlsx');
        $this->info($path);
        Excel::selectSheetsByIndex(0)->load($path, function($reader) {
            $result = $reader->get();
            $this->info('ingresando al modulo');
            $this->info($result->count());
            //  foreach($result as $row)
            //  {
            //      $this->info($row);
            //  }
        });
    }
}
