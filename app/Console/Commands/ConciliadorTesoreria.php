<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
class ConciliadorTesoreria extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tesoreria:Conciliador';

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
        $this->info("Consiliador Tesoreria");
        $extractos = DB::table('extracto_bancario')->where('conciliado',0)->get();
        $bar = $this->output->createProgressBar(count($extractos));
        foreach($extractos as $extracto){
            $mayor = DB::table('mayores_sigep')->where('nro_doc','like','%'.$extracto->nro_doc)
                                                ->where('monto','=',$extracto->monto)
                                                ->first();
            if($mayor)
            {   
                DB::table('extracto_bancario')
                    ->where('id', $extracto->id)
                    ->update(['conciliado' => 1]);
                
                DB::table('mayores_sigep')
                    ->where('id', $mayor->id)
                    ->update(['id_extrato_bancario' => $extracto->id]);
                
            }else{
                $saldo_anterior = DB::table('saldos_anteriores')->where('nro_doc','like','%'.$extracto->nro_doc)
                                                                ->where('monto','=',$extracto->monto)
                                                                ->first();
                if($saldo_anterior)
                {
                    DB::table('extracto_bancario')
                    ->where('id', $extracto->id)
                    ->update(['conciliado' => 1]);
                
                    DB::table('saldos_anteriores')
                    ->where('id', $saldo_anterior->id)
                    ->update(['id_extrato_bancario' => $extracto->id]);
                }
            }
            $bar->advance();
            
        }
        $bar->finish();
    }
}
