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
        //
        Schema::create('payables', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_id');
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('cascade'); // if the customer is deleted, all the receivable also gone
            $table->double("amount");
            $table->double("remaining");
            $table->String("status");
            $table->String("payment_term");
            $table->dateTime("date");
            $table->dateTime("dueDate");
            $table->String("remark")->nullable();
            $table->String("attachment")->nullable();
            $table->boolean('isArchive');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('payables');
    }
};
