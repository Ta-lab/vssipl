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
        Schema::create('sales_despatch_plan_transactions', function (Blueprint $table) {
            $table->id();
            $table->integer('plan_id');
            $table->date('open_date');
            $table->integer('cus_id');
            $table->integer('part_id');
            $table->integer('rc_id');
            $table->integer('prc_id');
            $table->integer('stricker_id');
            $table->integer('cover_qty')->default(0);
            $table->integer('receive_qty')->default(0);
            $table->integer('to_confirm_qty')->default(0);
            $table->integer('invoiced_qty')->default(0);
            $table->integer('status')->default(1);
            $table->string('remarks')->nullable();
            $table->integer('prepared_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_despatch_plan_transactions');
    }
};
