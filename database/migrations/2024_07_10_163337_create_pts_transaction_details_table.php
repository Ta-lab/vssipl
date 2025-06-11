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
        Schema::create('pts_transaction_details', function (Blueprint $table) {
            $table->id();
            $table->date('open_date');
            $table->integer('part_id');
            $table->integer('process_id');
            $table->string('process');
            $table->integer('rc_id');
            $table->integer('previous_rc_id');
            $table->float('issue_qty', 8, 2)->default(0);
            $table->float('receive_qty', 8, 2)->default(0);
            $table->float('reject_qty', 8, 2)->default(0);
            $table->float('rework_qty', 8, 2)->default(0);
            $table->float('return_issue_qty', 8, 2)->default(0);
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
        Schema::dropIfExists('pts_transaction_details');
    }
};
