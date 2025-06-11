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
        Schema::create('packing_cover_details', function (Blueprint $table) {
            $table->id();
            $table->string('cover_name');
            $table->string('cover_size');
            $table->float('cover_width', 8, 2)->default(0);
            $table->float('cover_height', 8, 2)->default(0);
            $table->float('cover_thickness', 8, 2)->default(0);
            $table->float('cover_weight', 8, 2)->default(0);
            $table->float('cover_color', 8, 2)->default(0);
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
        Schema::dropIfExists('packing_cover_details');
    }
};
