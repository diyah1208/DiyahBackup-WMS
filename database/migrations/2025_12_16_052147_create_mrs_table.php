<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tb_material_request', function (Blueprint $table) {
            $table->bigIncrements('mr_id');

            $table->string('mr_kode')->unique();          
            $table->string('mr_lokasi');  
            $table->string('mr_pic');                
            $table->unsignedBigInteger('mr_pic_id');      
            $table->date('mr_tanggal')->nullable();
            $table->date('mr_due_date')->nullable();
            $table->string('mr_status')->default('open');

            $table->timestamps();

            $table->foreign('mr_pic_id')->references('id')->on('users');
        });

    }   

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mrs');
    }
};
