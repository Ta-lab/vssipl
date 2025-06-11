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
        Schema::create('rm_requistion_grn_details', function (Blueprint $table) {
            $table->id();
            $table->date('open_date');
            $table->integer('req_rc_id');
            $table->integer('issue_rc_id');
            $table->integer('part_id');
            $table->integer('rm_id');
            $table->integer('grn_id');
            $table->integer('grn_qc_id');
            $table->integer('heat_id');
            $table->integer('req_type_id')->default(1);
            $table->float('req_kg', 8, 2)->default(0);
            $table->integer('req_qty')->default(0);
            $table->float('to_be_return_kg', 8, 2)->default(0);
            $table->integer('to_be_return_qty')->default(0);
            $table->float('return_kg', 8, 2)->default(0);
            $table->integer('return_qty')->default(0);
            $table->integer('return_status')->default(0);
            $table->float('issue_kg', 8, 2)->default(0);
            $table->integer('issue_qty')->default(0);
            $table->integer('request_by');
            $table->integer('approve_by')->nullable();
            $table->integer('status')->default(0);
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
        Schema::dropIfExists('rm_requistion_grn_details');
    }
};
