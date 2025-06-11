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
        Schema::create('machine_masters', function (Blueprint $table) {
            $table->id();
            $table->string('machine_name')->unique();
            $table->integer('machine_id');
            $table->integer('cell_id');
            $table->integer('group_id');
            $table->integer('maintainance_days')->default(0);
            $table->date('maintainance_date');
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
        Schema::dropIfExists('machine_masters');
    }
};
