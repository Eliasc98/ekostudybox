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
        Schema::create('generated_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code_generated')->unique();
            $table->integer('number_of_months');
            $table->integer('number_of_users');
            $table->string('platform');
            $table->string('client_name');
            $table->string('client_phone_number');
            $table->string('client_email_address');
            $table->text('client_remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generated_codes');
    }
};
