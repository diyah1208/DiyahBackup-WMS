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
        Schema::create('dtl_delivery', function (Blueprint $table) {
            $table->bigIncrements('dtl_dlv_id');

            $table->unsignedBigInteger('dlv_id');
            $table->unsignedBigInteger('part_id');

            $table->string('dtl_dlv_part_number');
            $table->string('dtl_dlv_part_name');
            $table->string('dtl_dlv_satuan');

            $table->integer('qty_on_delivery');    
            $table->integer('qty_delivered');   
            $table->integer('qty_pending');   

            $table->timestamps();

            $table->foreign('dlv_id')->references('dlv_id')->on('tb_delivery')->onDelete('cascade');
            $table->foreign('part_id')->references('part_id')->on('tb_barang');
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delive_detail');
    }
};
