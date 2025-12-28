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
        Schema::create('dtl_purchase_request', function (Blueprint $table) {
            $table->bigIncrements('dtl_pr_id');

            $table->unsignedBigInteger('pr_id');
            $table->unsignedBigInteger('mr_id');
            $table->unsignedBigInteger('part_id');

            $table->string('dtl_pr_part_number');
            $table->string('dtl_pr_part_name');
            $table->string('dtl_pr_satuan');
            $table->integer('dtl_pr_qty');

            $table->timestamps();

            $table->foreign('pr_id')->references('pr_id')->on('tb_purchase_request')->onDelete('cascade');
            $table->foreign('mr_id')->references('mr_id')->on('tb_material_request');
            $table->foreign('part_id')->references('part_id')->on('tb_barang');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pr_items');
    }
};
