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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('supplier_code')->unique();
            $table->string('supplier_type')->default(0);
            $table->string('name');
            $table->string('gst_number');
            $table->string('address');
            $table->string('address1');
            $table->string('city');
            $table->string('state');
            $table->integer('state_code');
            $table->integer('pincode');
            $table->string('contact_person')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->bigInteger('contact_number')->nullable();
            $table->string('purchasetype')->nullable();
            $table->string('payment_terms')->nullable();
            $table->string('packing_charges');
            $table->string('trans_mode');
            $table->integer('cgst')->default(0);
            $table->integer('sgst')->default(0);
            $table->integer('igst')->default(0);
            $table->text('remarks')->nullable();
            $table->integer('currency_id');
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
        Schema::dropIfExists('suppliers');
    }
};
