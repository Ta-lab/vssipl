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
        Schema::create('packing_stricker_details', function (Blueprint $table) {
            $table->id();
            $table->integer('part_id');
            $table->integer('rc_id');
            $table->integer('packing_master_id')->default(0);
            $table->integer('cover_order_id');
            $table->integer('cover_id');
            $table->integer('cover_qty')->default(0);
            $table->integer('total_cover_qty')->default(0);
            $table->integer('total_receive_qty')->default(0);
            $table->integer('ok_packed_qty')->default(0);
            $table->integer('reject_packed_qty')->default(0);
            $table->integer('rework_packed_qty')->default(0);
            $table->integer('pts_dc_issue_qty')->default(0);
            $table->integer('u1_dc_reject_qty')->default(0);
            $table->integer('u1_dc_receive_qty')->default(0);
            $table->integer('invoice_qty')->default(0);
            $table->integer('status')->default(0);
            $table->integer('print_status')->default(0);
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
        Schema::dropIfExists('packing_stricker_details');
    }
};
