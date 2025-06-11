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
        Schema::create('dc_variation_rc_statuses', function (Blueprint $table) {
            $table->id();
            $table->integer('previous_dc_id')->default(0);
            $table->integer('dc_id')->unique();
            $table->integer('rc_status')->default(1);
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
        Schema::dropIfExists('dc_variation_rc_statuses');
    }
};
