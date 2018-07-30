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
        
        Schema::create('afiliados_comando', function(Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('fecha');
            $table->string('nit');
            $table->integer('ci');
            $table->string('ext')->nullable();
            $table->string('paterno')->nullable();
            $table->string('materno')->nullable();
            $table->string('primer_nombre');
            $table->string('segundo_nombre')->nullable();
            $table->decimal('descuento',8,2);
            $table->timestamps();
        });
        Schema::create('afiliados_senasir', function(Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('fecha');
            $table->string('regional');
            $table->string('aseguradora');
            $table->string('matricula')->nullable();
            $table->string('matricula_dh')->nullable();
            $table->string('paterno')->nullable();
            $table->string('materno')->nullable();
            $table->string('nombres');
            $table->decimal('descuento',8,2);
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
