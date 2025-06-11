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
        Schema::create('pts_transaction_summaries', function (Blueprint $table) {
            $table->id();
            $table->date('open_date');
            $table->integer('part_id');
            $table->integer('process_id');
            $table->string('process');
            $table->integer('rc_id');
            $table->float('u1_dc_issue_qty', 8, 2)->default(0);
            $table->float('pts_store_dc_receive_qty', 8, 2)->default(0);
            $table->float('pts_store_dc_reject_qty', 8, 2)->default(0);
            $table->float('pts_store_dc_rework_qty', 8, 2)->default(0);
            $table->float('pts_production_receive_qty', 8, 2)->default(0);
            $table->float('pts_production_issue_qty', 8, 2)->default(0);
            $table->float('pts_production_reject_qty', 8, 2)->default(0);
            $table->float('pts_production_rework_qty', 8, 2)->default(0);
            $table->float('cle_receive_qty', 8, 2)->default(0);
            $table->float('cle_issue_qty', 8, 2)->default(0);
            $table->float('cle_reject_qty', 8, 2)->default(0);
            $table->float('cle_rework_qty', 8, 2)->default(0);
            $table->float('cle_return_qty', 8, 2)->default(0);
            $table->float('pts_store_dc_issue_qty', 8, 2)->default(0);
            $table->float('u1_dc_receive_qty', 8, 2)->default(0);
            $table->float('u1_dc_reject_qty', 8, 2)->default(0);
            $table->float('u1_dc_rework_qty', 8, 2)->default(0);
            $table->float('u1_dc_return_qty', 8, 2)->default(0);
            $table->string('remarks')->nullable();
            $table->integer('status')->default(1);
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
        Schema::dropIfExists('pts_transaction_summaries');
    }
};
