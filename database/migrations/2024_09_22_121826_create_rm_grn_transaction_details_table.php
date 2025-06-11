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
        Schema::create('rm_grn_transaction_details', function (Blueprint $table) {
            $table->id();
            $table->date('open_date');
            $table->integer('requistion_id');
            $table->integer('rc_id')->unique();
            $table->integer('grn_id');
            $table->integer('grn_qc_id');
            $table->integer('rm_id');
            $table->integer('part_id');
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
        Schema::dropIfExists('rm_grn_transaction_details');
    }
};
