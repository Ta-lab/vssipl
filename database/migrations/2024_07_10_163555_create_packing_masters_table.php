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
        Schema::create('packing_masters', function (Blueprint $table) {
            $table->id();
            $table->integer('cus_id');
            $table->string('cus_type_name');
            $table->integer('part_id');
            $table->integer('cover_id');
            $table->float('cover_qty', 8, 2)->default(0);
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
        Schema::dropIfExists('packing_masters');
    }
};
