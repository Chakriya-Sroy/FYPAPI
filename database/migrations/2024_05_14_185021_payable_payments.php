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
        Schema::create('payable_payments', function (Blueprint $table) {
            $table->id();
            $table->integer('payable_id')->unsigned();
            $table->foreign('payable_id')->references('id')->on('payables')->onDelete('cascade'); // if the customer is deleted, all the receivable also gone
            $table->double("amount");
            $table->dateTime("date");
            $table->String("remark")->nullable();
            $table->String("attachment")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payable_payments');
    }
};
