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
        Schema::create('rm_requistions', function (Blueprint $table) {
            $table->id();
            $table->integer('rc_id')->unique();
            $table->date('open_date');
            $table->integer('part_id');
            $table->integer('rm_id');
            $table->integer('machine_id');
            $table->integer('group_id');
            $table->integer('req_type_id')->default(1);
            $table->float('req_kg', 8, 2)->default(0);
            $table->integer('req_qty')->default(0);
            $table->float('to_be_return_kg', 8, 2)->default(0);
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
        Schema::dropIfExists('rm_requistions');
    }
};
