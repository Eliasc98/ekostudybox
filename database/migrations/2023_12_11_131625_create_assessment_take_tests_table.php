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
        Schema::create('assessment_take_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('year_id')->constrained();
            $table->foreignId('assessment_test_taken_id')->constrained()->nullable();
            $table->foreignId('subject_id')->constrained();
            $table->string('num_question')->nullable(false);
            $table->integer('correctly_answ')->nullable(false);
            $table->integer('wrongly_answ')->nullable(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_take_tests');
    }
};
