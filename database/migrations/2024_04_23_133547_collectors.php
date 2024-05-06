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
         Schema::create('collectors', function (Blueprint $table) {
            $table->id();
            $table->foreign('user_id')->references('id')->on('user')->onDelete('cascade'); // user_id
            $table->integer('user_id')->unsigned();
            $table->foreign('collector_id')->references('id')->on('user')->onDelete('cascade'); // collector_id
            $table->integer('collector_id')->unsigned();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('collectors');
    }
};
