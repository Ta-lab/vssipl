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
        Schema::create('firewall_inspection_details', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('name');
            $table->string('inspection_area');
            $table->string('unit_name');
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
        Schema::dropIfExists('firewall_inspection_details');
    }
};
