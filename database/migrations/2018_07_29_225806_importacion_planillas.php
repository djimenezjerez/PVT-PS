<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ImportacionPlanillas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        
        // Schema::create('afiliados_comando', function(Blueprint $table) {
        //     $table->bigIncrements('id');
        //     $table->date('fecha');
        //     $table->string('nit');
        //     $table->integer('ci');
        //     $table->string('ext')->nullable();
        //     $table->string('paterno')->nullable();
        //     $table->string('materno')->nullable();
        //     $table->string('primer_nombre');
        //     $table->string('segundo_nombre')->nullable();
        //     $table->decimal('descuento',8,2);
        //     $table->string('tipo')->nulleable()->default(null);
        //     $table->timestamps();
        // });
        // Schema::create('afiliados_senasir', function(Blueprint $table) {
        //     $table->bigIncrements('id');
        //     $table->date('fecha');
        //     $table->string('regional');
        //     $table->string('aseguradora');
        //     $table->string('matricula')->nullable();
        //     $table->string('matricula_dh')->nullable();
        //     $table->string('paterno')->nullable();
        //     $table->string('materno')->nullable();
        //     $table->string('nombres');
        //     $table->decimal('descuento',8,2);
        //     $table->timestamps();
        // });
        
        Schema::create('mayores_sigep', function(Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('fecha');
            $table->string('comprobante');
            $table->string('identificador');
            $table->string('cod_sigep')->nullable();
            $table->decimal('monto', 12, 2);
            $table->decimal('debe', 12, 2);
            $table->decimal('haber', 12, 2);
            $table->string('linea')->nullable();
            $table->string('beneficiario');
            $table->string('nro_doc');
            $table->bigInteger('id_extrato_bancario')->nullable();
            $table->timestamps();
        });

        Schema::create('extracto_bancario', function(Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('fecha');
            $table->string('agencia');
            $table->string('descripcion');
            $table->string('nro_doc');
            $table->decimal('monto', 12, 2);
            $table->boolean('conciliado')->default(false);
            $table->timestamps();
        });
        Schema::create('saldos_anteriores', function(Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('fecha');
            $table->string('comprobante');
            $table->string('cod_sigep');
            $table->string('nro_doc');
            $table->string('beneficiario');
            $table->decimal('monto', 12, 2);
            $table->bigInteger('id_extrato_bancario')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
