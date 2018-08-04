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
        $this->info("Ordenando Garantes");
        $path = storage_path('cuotas.xlsx');
        Excel::selectSheetsByIndex(0)->load($path , function($reader) {
            
            // reader methods
            global $rows,$rows_not_found;
            
            $rows = array();
            $rows_not_found = array();
            array_push($rows, array('nro_prestamo','fecha_desembolso','producto','matricula', 'paterno', 'materno', 'primer_nombre','segundo_nombre', 'capital','interes','interes_penal','otros_cobros','total_pagado','*','ci','paterno','materno','primer_nombre','segundo_nombre','descuento'));
            array_push($rows_not_found, array('nro_prestamo','fecha_desembolso','producto','matricula', 'paterno', 'materno', 'primer_nombre','segundo_nombre', 'capital','interes','interes_penal','otros_cobros','total_pagado','*','ci','paterno','materno','primer_nombre','segundo_nombre','descuento'));
            // $rows = array();

            $result = $reader->select(array('nro_prestamo','fecha_desembolso','producto','matricula', 'paterno', 'materno', 'primer_nombre','segundo_nombre', 'capital','interes','interes_penal','otros_cobros','total_pagado'))
                            ->take(100)
                            ->get();
            foreach($result as $row){
                
                $arr= explode('-',$row->matricula);    
                $ci= $arr[0];
                $afiliado = DB::table('afiliados_comando')->where('ci',$ci)->first();
                if( isset($afiliado->id)){
                    
                    DB::table('afiliados_comando')
                        ->where('id', $afiliado->id)
                        ->update(['tipo' => 'garante']);
                    array_push($rows,array($row->nro_prestamo,$row->fecha_desembolso,$row->fecha,$row->producto,$row->matricula,$row->paterno,$row->materno,$row->primer_nombre,$row->segundo_nombre,$row->capital,$row->interes,$row->interes_penal,$row->otros_cobros,$row->total_pagado,'*',$afiliado->ci,$afiliado->paterno,$afiliado->materno,$afiliado->primer_nombre,$afiliado->segundo_nombre,number_format($afiliado->descuento, 2, ',', '')));
                    $this->info($row);
                }else{
                    array_push($rows,array($row->nro_prestamo,$row->fecha_desembolso,$row->producto,$row->matricula,$row->paterno,$row->materno,$row->primer_nombre,$row->segundo_nombre,$row->capital,$row->interes,$row->interes_penal,$row->otros_cobros,$row->total_pagado,'*'));
                    
                }
                //$this->info($row);
                
            }




        });

        Excel::create('garantes_conciliacion',function($excel)
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
                    // $excel->sheet('no_captital_0',function($sheet) {
                    //         global $rows,$rows_not_found,$row_empy_capital;
                    //         $sheet->fromModel($row_empy_capital,null, 'A1', false, false);
                    //         $sheet->cells('A1:C1', function($cells) {
                    //         // manipulate the range of cells
                    //         $cells->setBackground('#058A37');
                    //         $cells->setFontColor('#ffffff');  
                    //         $cells->setFontWeight('bold');
                    //         });
                    //     });
        })->store('xls', storage_path());
        $this->info('Finished');
    }
}
