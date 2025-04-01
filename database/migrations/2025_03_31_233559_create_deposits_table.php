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
        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('loan_id');
            $table->integer('amount');
            $table->string('status')->default('creado');
            $table->string('observation', 500)->nullable();
            $table->unsignedBigInteger('file_id')->nullable();
            $table->string('type', 20)->default('nequi');
            $table->string('reference', 50)->nullable();
            $table->string('nequi', 150)->nullable();
            $table->timestamp('date_transaction')->nullable();
            $table->foreign('loan_id')->references('id')->on('loans')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deposits');
    }
};
