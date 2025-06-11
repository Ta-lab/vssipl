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
        Schema::create('lock_mechanisms', function (Blueprint $table) {
            $table->id();
            $table->date('open_date');
            $table->string('department');
            $table->string('main_activity');
            $table->string('sub_activity');
            $table->integer('no_days')->default(0);
            $table->integer('status')->default(1);
            $table->integer('deviation_status')->default(0);
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
        Schema::dropIfExists('lock_mechanisms');
    }
};
