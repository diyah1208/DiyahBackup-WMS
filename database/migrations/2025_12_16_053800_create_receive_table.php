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
        Schema::create('tb_receive_item', function (Blueprint $table) {
            $table->bigIncrements('ri_id');

            $table->string('ri_kode')->unique();         
            $table->unsignedBigInteger('po_id');          
            $table->string('ri_lokasi'); 
            $table->string('ri_pic'); 
            $table->date('ri_tanggal');
            $table->text('ri_keterangan')->nullable();

            $table->timestamps();

            $table->foreign('po_id')->references('po_id')->on('tb_purchase_order');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receive');
    }
};
