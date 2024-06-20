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
        Schema::create('receivable_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('receivable_id');
            $table->foreign('receivable_id')->references('id')->on('receivables')->onDelete('cascade'); // if the customer is deleted, all the receivable also gone
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade'); // if the customer is deleted, all the receivable also gone
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
        Schema::dropIfExists('receivable_payments');
    }
};
