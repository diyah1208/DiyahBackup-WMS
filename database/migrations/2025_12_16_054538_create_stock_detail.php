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
        Schema::create('tb_stock', function (Blueprint $table) {
            $table->bigIncrements('stk_id');      
            $table->integer('stk_qty')->default(0);
            $table->integer('stk_min')->default(0);
            $table->integer('stk_max')->default(0);
            $table->string('stk_location');  

            $table->timestamps();

        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_detail');
    }
};
