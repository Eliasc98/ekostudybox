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
        Schema::create('admin_topics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_subject_id')->constrained();
            $table->string('author');
            $table->string('topics');
            $table->integer('week');
            $table->string('content_status')->default('empty');
            $table->integer('num_of_test_questions')->default(0);
            $table->string('status')->default('false');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_topics');
    }
};
