<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
        \App\Console\Commands\CreateScript::class,
        \App\Console\Commands\OrderComand::class,
        \App\Console\Commands\OrderGar::class,
        \App\Console\Commands\checkdb::class,
        \App\Console\Commands\ImportComando::class,
        \App\Console\Commands\ImportSenasir::class,
        \App\Console\Commands\Sismu::class,
        \App\Console\Commands\Test::class,
        \App\Console\Commands\CheckGar::class,
        \App\Console\Commands\GetFormat::class,
        \App\Console\Commands\DepurarComando::class,
        \App\Console\Commands\AltasComando::class,
        \App\Console\Commands\ProcesarNoEncontrados::class,
        \App\Console\Commands\OnlyGar::class,
        \App\Console\Commands\DepurarPrestamos::class,
        \App\Console\Commands\DepurarGarantes::class,
        \App\Console\Commands\DepurarPresGar::class,
        \App\Console\Commands\DepurarPrestamosCancelados::class,
        \App\Console\Commands\CheckCommand::class,
        \App\Console\Commands\NuevosPrestamos::class,
        \App\Console\Commands\PrestamosExactos::class,
        \App\Console\Commands\IndevidosPrimeraCuota::class,
        \App\Console\Commands\PlanillaComando::class,
        \App\Console\Commands\TratadoEspecial::class,
        \App\Console\Commands\CobranzasConsilidador::class,
        \App\Console\Commands\CobranzasCancelados::class,
        \App\Console\Commands\CobranzasPendientes::class,
        \App\Console\Commands\CobranzasComprobantes::class,
        \App\Console\Commands\ImportSigepMayor::class,
        \App\Console\Commands\ImportSigepMayor::class,
        \App\Console\Commands\ImportSaldosAnteriores::class,
        \App\Console\Commands\ConciliadorTesoreria::class,
        \App\Console\Commands\SenasirCompleteData::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
