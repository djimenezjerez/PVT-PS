<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use DB;

class OrderComand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prestamos:OrderCommand';

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

        global $rows,$rows_garantes,$rows_not_found;

        $this->info("Ordenando XD");
        // $path = storage_path('final_planilla.xlsx');
        $path = storage_path('sismu_agilizando_oficial.xlsx');
        $this->info($path);
        Excel::selectSheetsByIndex(0)->load($path , function($reader) {
            
            // reader methods
            global $rows,$rows_garantes,$rows_not_found;
            
            $rows = array();
            $rows_not_found = array();
            $rows_garantes = array();
            array_push($rows, array('nro_prestamo','fecha_desembolso','producto','matricula', 'paterno', 'materno', 'primer_nombre','segundo_nombre', 'capital','interes','interes_penal','otros_cobros','total_pagado','tipo_descuento','nro_comprobante','*','ci','paterno','materno','primer_nombre','segundo_nombre','descuento'));
            array_push($rows_garantes, array('nro_prestamo','fecha_desembolso','producto','matricula', 'paterno', 'materno', 'primer_nombre','segundo_nombre', 'capital','interes','interes_penal','otros_cobros','total_pagado','tipo_descuento','nro_comprobante','*','ci','paterno','materno','primer_nombre','segundo_nombre','descuento'));
            array_push($rows_not_found, array('nro_prestamo','fecha_desembolso','producto','matricula', 'paterno', 'materno', 'primer_nombre','segundo_nombre', 'capital','interes','interes_penal','otros_cobros','total_pagado','tipo_descuento','nro_comprobante','*','ci','paterno','materno','primer_nombre','segundo_nombre','descuento'));
            // $rows = array();

            $result = $reader->select(array('nro_prestamo','fecha_desembolso','producto','matricula', 'paterno', 'materno', 'primer_nombre','segundo_nombre', 'capital','interes','interes_penal','otros_cobros','total_pagado','tipo_descuento','nro_comprobante'))
                           // ->take(100)
                            ->get();
            foreach($result as $row){
                
                $arr= explode('-',$row->matricula);    
                $ci= $arr[0];
                $afiliado = DB::table('afiliados_comando')
                                ->where('ci',$ci)
                                //->where('tipo','=','')
                                ->first();
                if( isset($afiliado->id)){
                    
                    $total = $row->total_pagado;
                    if($total == $afiliado->descuento)
                    {
                        DB::table('afiliados_comando')
                            ->where('id', $afiliado->id)
                            ->update(['tipo' => $row->tipo_descuento]);
                        
                        if(trim($row->tipo_descuento)=="GARANTE")
                        {
                           $this->info('------------------garante------------------');
                            array_push($rows_garantes,array($row->nro_prestamo,$row->fecha_desembolso,$row->producto,$row->matricula,$row->paterno,$row->materno,$row->primer_nombre,$row->segundo_nombre,$row->capital,$row->interes,$row->interes_penal,$row->otros_cobros,$row->total_pagado,$row->tipo_descuento,$row->nro_comprobante,'*',$afiliado->ci,$afiliado->paterno,$afiliado->materno,$afiliado->primer_nombre,$afiliado->segundo_nombre,number_format($afiliado->descuento, 2, ',', '')));
                        }else{
                            array_push($rows,array($row->nro_prestamo,$row->fecha_desembolso,$row->producto,$row->matricula,$row->paterno,$row->materno,$row->primer_nombre,$row->segundo_nombre,$row->capital,$row->interes,$row->interes_penal,$row->otros_cobros,$row->total_pagado,$row->tipo_descuento,$row->nro_comprobante,'*',$afiliado->ci,$afiliado->paterno,$afiliado->materno,$afiliado->primer_nombre,$afiliado->segundo_nombre,number_format($afiliado->descuento, 2, ',', '')));
                        }
                        $this->info($row);
                        
                    }else{
                        array_push($rows_not_found,array($row->nro_prestamo,$row->fecha_desembolso,$row->producto,$row->matricula,$row->paterno,$row->materno,$row->primer_nombre,$row->segundo_nombre,$row->capital,$row->interes,$row->interes_penal,$row->otros_cobros,$row->total_pagado,$row->tipo_descuento,$row->nro_comprobante,'*'));
                    }
                    
                }
                // else{
                //     array_push($rows_not_found,array($row->nro_prestamo,$row->fecha_desembolso,$row->producto,$row->matricula,$row->paterno,$row->materno,$row->primer_nombre,$row->segundo_nombre,$row->capital,$row->interes,$row->interes_penal,$row->otros_cobros,$row->total_pagado,'*'));
                    
                // }
                //$this->info($row);
                
            }




        });

        Excel::create('primera_conciliacion',function($excel)
        {
            global $rows,$rows_garantes,$rows_not_found;
                    $excel->sheet('conciliados_desc_auto',function($sheet) {
                            global $rows,$rows_garantes,$rows_not_found;
                            $sheet->fromModel($rows,null, 'A1', false, false);
                            $sheet->cells('A1:C1', function($cells) {
                            // manipulate the range of cells
                            $cells->setBackground('#058A37');
                            $cells->setFontColor('#ffffff');  
                            $cells->setFontWeight('bold');
                            });
                        });
                    $excel->sheet('conciliados_garantes',function($sheet) {
                            global $rows,$rows_garantes,$rows_not_found;
                            $sheet->fromModel($rows_garantes,null, 'A1', false, false);
                            $sheet->cells('A1:C1', function($cells) {
                            // manipulate the range of cells
                            $cells->setBackground('#058A37');
                            $cells->setFontColor('#ffffff');  
                            $cells->setFontWeight('bold');
                            });
                        });
                    $excel->sheet('nuevo_sismu_planilla',function($sheet) {
                            global $rows,$rows_garantes,$rows_not_found;
                            $sheet->fromModel($rows_not_found,null, 'A1', false, false);
                            $sheet->cells('A1:C1', function($cells) {
                            // manipulate the range of cells
                            $cells->setBackground('#058A37');
                            $cells->setFontColor('#ffffff');  
                            $cells->setFontWeight('bold');
                            });
                        });
        })->store('xls', storage_path());
        $this->info('Finished');
    }
}
