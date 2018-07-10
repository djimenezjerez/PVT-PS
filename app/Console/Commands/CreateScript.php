<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;

class CreateScript extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'keyrus:Script';

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
        $this->info('Importando del excel hpd');
        $path = storage_path('lista.xlsx');
        $this->info($path);
        Excel::load($path, function($reader) {
            $results = $reader->select(array('auxliar', 'capital'))
                               ->take(40)
                               ->get();
            $myfile = fopen(storage_path("runthisoficial.ahk"), "w") or die("Unable to open file!");
            $line ="Run, sigepmv2018.exe";
            fwrite($myfile,$line.PHP_EOL);
            $line ="WinWait, Error Fatal, ";
            fwrite($myfile,$line.PHP_EOL);
            $line ="IfWinNotActive, Error Fatal, , WinActivate, Error Fatal, ";
            fwrite($myfile,$line.PHP_EOL);
            $line ="WinWaitActive, Error Fatal, ";
            fwrite($myfile,$line.PHP_EOL);
            $line ="MouseClick, left,  352,  134";
            fwrite($myfile,$line.PHP_EOL);
            $line ="Sleep, 100";
            fwrite($myfile,$line.PHP_EOL);
            $line ="WinWait, SISTEMA DE GESTION PUBLICA, ";
            fwrite($myfile,$line.PHP_EOL);
            $line ="IfWinNotActive, SISTEMA DE GESTION PUBLICA, , WinActivate, SISTEMA DE GESTION PUBLICA, ";
            fwrite($myfile,$line.PHP_EOL);
            $line ="WinWaitActive, SISTEMA DE GESTION PUBLICA, ";
            fwrite($myfile,$line.PHP_EOL);
            $line ="MouseClick, left,  224,  135";
            fwrite($myfile,$line.PHP_EOL);
            $line ="Sleep, 100";
            fwrite($myfile,$line.PHP_EOL);
            $line ="WinWait, SigepMV V.6.0.0-[0345-DE-Mutual de Servicios al Policía], ";
            fwrite($myfile,$line.PHP_EOL);
            $line ="IfWinNotActive, SigepMV V.6.0.0-[0345-DE-Mutual de Servicios al Policía], , WinActivate, SigepMV V.6.0.0-[0345-DE-Mutual de Servicios al Policía], ";
            fwrite($myfile,$line.PHP_EOL);
            $line ="WinWaitActive, SigepMV V.6.0.0-[0345-DE-Mutual de Servicios al Policía], ";
            fwrite($myfile,$line.PHP_EOL);
            $line ="MouseClick, left,  51,  83";
            fwrite($myfile,$line.PHP_EOL);
            $line ="Sleep, 100";
            fwrite($myfile,$line.PHP_EOL);
            $line ="MouseClick, left,  727,  514";
            fwrite($myfile,$line.PHP_EOL);
            $line ="Sleep, 100";
            fwrite($myfile,$line.PHP_EOL);
            $line ="MouseClick, left,  75,  152";
            fwrite($myfile,$line.PHP_EOL);
            $line ="Sleep, 100";
            fwrite($myfile,$line.PHP_EOL);
            $line ="MouseClick, left,  61,  378";
            fwrite($myfile,$line.PHP_EOL);
            $line ="Sleep, 100";
            fwrite($myfile,$line.PHP_EOL);
            $line ="MouseClick, left,  545,  319";
            fwrite($myfile,$line.PHP_EOL);
            $line ="Sleep, 100";
            fwrite($myfile,$line.PHP_EOL);
            $line ="Send, beneficiarop{TAB}{TAB}documento{SPACE}{BACKSPACE}{TAB}{TAB}{TAB}12163";//0.1 100000 capital
            fwrite($myfile,$line.PHP_EOL);
            $line ="Sleep, 100";
            fwrite($myfile,$line.PHP_EOL);
            $line ="Send, 1216323.01";
            fwrite($myfile,$line.PHP_EOL);
            $line ="Sleep, 100";
            fwrite($myfile,$line.PHP_EOL);
            $line ="Send, 2710110.8{ENTER}";
            fwrite($myfile,$line.PHP_EOL);
            $line ="MouseClick, left,  460,  283";
            fwrite($myfile,$line.PHP_EOL);

            $contador =1;

            foreach($results as $row){
                //fwrite($myfile
                //$contador++;
                fwrite($myfile,'Sleep, 500'.PHP_EOL);
                //Send, 121631216330051{RIGHT}706.91{ENTER}{DOWN}{LEFT}
                $txt = "Send,  12163";
                fwrite($myfile, $txt.PHP_EOL);
                fwrite($myfile,'Sleep, 500'.PHP_EOL);
                $txt = "Send,  ".trim($row->auxliar)."{RIGHT}";
                fwrite($myfile, $txt.PHP_EOL);
                fwrite($myfile,'Sleep, 500'.PHP_EOL);
                fwrite($myfile,'Send, '.trim($row->capital).'{ENTER}'.PHP_EOL);
                fwrite($myfile,'Sleep, 500'.PHP_EOL);
                fwrite($myfile,'Send, {ENTER}{DOWN}'.PHP_EOL);
                
                //cuando termina las 20 lineas
                if($contador<19)
                {
                    $contador++;
                }else{
                    fwrite($myfile,'Sleep, 500'.PHP_EOL);
                    fwrite($myfile,'Send, {CTRLDOWN}n{CTRLUP}'.PHP_EOL);
                }
              //fwrite ($myfile,'\n');
            }
            //$this->info($results);
        });
    }
}
