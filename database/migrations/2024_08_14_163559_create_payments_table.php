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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lending_id');
            $table->timestamp('date')->nullable();
            $table->string('amount')->nullable();
            $table->string('observation', 500)->nullable();
            $table->string('status')->default('creado');
            $table->unsignedBigInteger('file_id')->nullable();
            $table->string('type', 20)->default('nequi');
            $table->string('reference', 50)->nullable();
            $table->foreign('lending_id')->references('id')->on('lendings')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
