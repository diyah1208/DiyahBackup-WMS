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
        Schema::create('tb_delivery', function (Blueprint $table) {
            $table->bigIncrements('dlv_id');

            $table->string('dlv_kode')->unique();          
            $table->unsignedBigInteger('mr_id');

            $table->string('dlv_dari_gudang');
            $table->string('dlv_ke_gudang');
            $table->string('dlv_ekspedisi');
            $table->integer('dlv_jumlah_koli')->default(0);
            $table->string('dlv_pic');
            $table->string('dlv_no_resi')->nullable();
            $table->string('dlv_status');

            $table->timestamps();

            $table->foreign('mr_id')->references('mr_id')->on('tb_material_request');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delive');
    }
};
