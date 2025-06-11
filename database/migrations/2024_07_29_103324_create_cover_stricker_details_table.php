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
        Schema::create('cover_stricker_details', function (Blueprint $table) {
            $table->id();
            $table->integer('part_id');
            $table->integer('rc_id');
            $table->integer('prc_id');
            $table->integer('stricker_id');
            $table->integer('total_cover_qty')->default(0);
            $table->integer('total_issue_qty')->default(0);
            $table->integer('total_receive_qty')->default(0);
            $table->integer('total_reject_qty')->default(0);
            $table->integer('total_rework_qty')->default(0);
            $table->integer('total_return_issue_qty')->default(0);
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
        Schema::dropIfExists('cover_stricker_details');
    }
};
