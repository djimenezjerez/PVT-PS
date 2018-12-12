<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use DB;
use Log;
use Datetime;
use Carbon\Carbon;
class EconomicComplementController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //

        // aumenta el tiempo máximo de ejecución de este script a 150 min: 
        ini_set ('max_execution_time', 9000); 
        // aumentar el tamaño de memoria permitido de este script: 
        ini_set ('memory_limit', '960M');
        
        $excel = request('excel')??'';
        $order = request('order')??'';
        $pagination_rows = request('pagination_rows')??10;
        $conditions = [];
        //filtros
        $ci = request('ci')??'';
        $primer_nombre = request('primer_nombre')??'';
        $segundo_nombre = request('segundo_nombre')??'';
        $apellido_paterno = request('apellido_paterno')??'';
        $apellido_materno = request('apellido_materno')??'';
        $message = request('message')??'';
        $amount_loan = request('amount_loan')??'';
        $is_enabled = request('is_enabled')??'';

        if($ci != '')
        {
            array_push($conditions,array('affiliates.identity_card','like',"%{$ci}%"));
        }
        if($primer_nombre != '')
        {
            array_push($conditions,array('affiliates.first_name','like',"%{$primer_nombre}%"));
        }
        if($segundo_nombre != '')
        {
            array_push($conditions,array('affiliates.second_name','like',"%{$segundo_nombre}%"));
        }
        if($apellido_paterno != '')
        {
            array_push($conditions,array('affiliates.last_name','like',"%{$apellido_paterno}%"));
        }
        if($apellido_materno != '')
        {
            array_push($conditions,array('affiliates.mother_last_name','like',"%{$apellido_materno}%"));
        }
        if($message != '')
        {
            array_push($conditions,array('eco_com_observations.message','like',"%{$message}%"));
        }
        if($is_enabled != '')
        {
            $is_enabled = $is_enabled=='SUBSANADO'?true:false;
            array_push($conditions,array('eco_com_observations.is_enabled','=',$is_enabled));
        }
        if($amount_loan != '')
        {
            array_push($conditions,array('economic_complements.amount_loan','=',$amount_loan));
        }

       
        
        if($excel!='')//reporte excel hdp 
        {
            global $rows_exacta;
            $rows_exacta = Array();
            //cabezera
            array_push($rows_exacta,array(
                                        'ID','CI','Extension','1er Nombre','2do Nombre','Paterno','Materno',
                                        'Mensaje','Amortizacion','Estado',
                                        ));

            $observados = DB::connection('virtual_platform')->table('economic_complements')
                ->join('eco_com_observations','eco_com_observations.economic_complement_id','=','economic_complements.id')
                ->join('affiliates','affiliates.id','=','economic_complements.affiliate_id')
                ->leftJoin('cities','cities.id','=','affiliates.city_identity_card_id')
                ->where('economic_complements.eco_com_procedure_id','=',13)
                ->where('eco_com_observations.observation_type_id','=',2)
                ->where('eco_com_observations.deleted_at',null)
                ->where($conditions)
                ->select("affiliates.id","affiliates.identity_card as ci","cities.first_shortened as ext","affiliates.first_name as primer_nombre","affiliates.second_name as segundo_nombre","affiliates.last_name as apellido_paterno","affiliates.mothers_last_name as apellido_materno","eco_com_observations.message",'economic_complements.amount_loan',"eco_com_observations.is_enabled","economic_complements.total")
                ->get();
            foreach($observados as $loan)
            {
                    array_push($rows_exacta,array(
                                                $loan->id,$loan->ci, $loan->ext,$loan->primer_nombre,$loan->segundo_nombre,$loan->apellido_paterno,$loan->apellido_materno,
                                                $loan->message,$loan->amount_loan,$loan->is_enabled?'subsanado':'vigente',
                                            ));    
            }
            Excel::create('Observados por Prestamos',function($excel)
            {
                global $rows_exacta;
                
                        $excel->sheet('observados con complemento',function($sheet) {
                                global $rows_exacta;
                
                                $sheet->fromModel($rows_exacta,null, 'A1', false, false);
                                $sheet->cells('A1:R1', function($cells) {
                                // manipulate the range of cells
                                $cells->setBackground('#058A37');
                                $cells->setFontColor('#ffffff');  
                                $cells->setFontWeight('bold');
                                });
                            });
    
                      
            })->download('xls');

        }else{
            
            $observados = DB::connection('virtual_platform')->table('economic_complements')
            ->join('eco_com_observations','eco_com_observations.economic_complement_id','=','economic_complements.id')
            ->join('affiliates','affiliates.id','=','economic_complements.affiliate_id')
            ->leftJoin('cities','cities.id','=','affiliates.city_identity_card_id')
            // ->leftJoin()
            ->where('economic_complements.eco_com_procedure_id','=',13)
            ->where('eco_com_observations.observation_type_id','=',2)
            ->where('eco_com_observations.deleted_at',null)
            ->where($conditions)
            ->select("affiliates.id","affiliates.identity_card as ci","cities.first_shortened as ext","affiliates.first_name as primer_nombre","affiliates.second_name as segundo_nombre","affiliates.last_name as apellido_paterno","affiliates.mothers_last_name as apellido_materno","eco_com_observations.message",'economic_complements.amount_loan',"eco_com_observations.is_enabled","economic_complements.total","economic_complements.id as complement_id")
            ->paginate($pagination_rows);

            $observados->getCollection()->transform(function ($item) {
                $note = DB::connection('virtual_platform')->table('eco_com_observations')->where('economic_complement_id','=',$item->complement_id)->where('observation_type_id','=',28)->first();
                if($note){
                    $item->note = utf8_encode(trim($note->message));
                }else{
                    $item->note= '';
                }
                return $item;
            });

            return response()->json($observados->toArray());
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
