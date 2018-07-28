<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use DB;
class checkdb extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'keyrus:CheckDB';

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
        // abrir en modo sólo lectura
        $this->info('Importando del excel hpd');
        $path = storage_path('mayo_2016.xlsx');
        global $rows,$rows_not_found,$row_empy_capital;
        $rows = array(array('nro'=>'nro','auxiliar'=>'auxiliar','capital'=>'capital'));
        $rows_not_found = array(array('nro'=>'nro','auxiliar'=>'auxiliar','capital'=>'capital'));
        $row_empy_capital = array(array('nro'=>'nro','auxiliar'=>'auxiliar','capital'=>'capital'));
        Excel::load($path, function($reader) {
            global $rows,$rows_not_found,$row_empy_capital;
            $results = $reader->select(array('nro','paterno','materno','nombre', 'capital'))
                              // ->take(100)
                               ->get();
         
            foreach($results as $row){
                $registro = DB::table('auxiliares')->where('RAZON','like','%'.$row->nro.'%')->first();
                if($registro)
                {
                    $new_reg = array('nro'=>$row->nro,'auxiliar'=>$registro->AUX,'capital'=>$row->capital );
                    if($row->capital>0){
                        array_push($rows, $new_reg);
                    }
                    else{
                        array_push($row_empy_capital,$new_reg);
                    }
                    $this->info($registro->AUX.' '.$row->nro.' '.$row->capital);
                }else{
                    //$this->info($row->nro.' not found');
                    $new_reg = array('nro'=>$row->nro,'auxiliar'=>'no encontrado','capital'=>$row->capital );
                    array_push($rows_not_found, $new_reg);
                }
                //$this->info($row->nro);         
            }
           // $this->info($results);
        });

        Excel::create('depurados_mayo_2016',function($excel)
        {
            global $rows,$rows_not_found,$row_empy_capital;
                    $excel->sheet('encontrados',function($sheet) {
                            global $rows,$rows_not_found,$row_empy_capital;
                            $sheet->fromModel($rows,null, 'A1', false, false);
                            $sheet->cells('A1:C1', function($cells) {
                            // manipulate the range of cells
                            $cells->setBackground('#058A37');
                            $cells->setFontColor('#ffffff');  
                            $cells->setFontWeight('bold');
                            });
                        });
                    $excel->sheet('no_encontrados',function($sheet) {
                            global $rows,$rows_not_found,$row_empy_capital;
                            $sheet->fromModel($rows_not_found,null, 'A1', false, false);
                            $sheet->cells('A1:C1', function($cells) {
                            // manipulate the range of cells
                            $cells->setBackground('#058A37');
                            $cells->setFontColor('#ffffff');  
                            $cells->setFontWeight('bold');
                            });
                        });
                    $excel->sheet('no_captital_0',function($sheet) {
                            global $rows,$rows_not_found,$row_empy_capital;
                            $sheet->fromModel($row_empy_capital,null, 'A1', false, false);
                            $sheet->cells('A1:C1', function($cells) {
                            // manipulate the range of cells
                            $cells->setBackground('#058A37');
                            $cells->setFontColor('#ffffff');  
                            $cells->setFontWeight('bold');
                            });
                        });
        })->store('xls', storage_path());

        $this->info("total correctos: ".sizeOf($rows));
        $this->info("total no encontrados: ".sizeOf($rows_not_found));
        
    }
}
