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
        Schema::create('guardians', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('whatsapp');
            $table->string('email')->unique()->nullable();
            $table->enum('status', ['nuevo', 'contactado', 'convertido', 'descartado'])->default('nuevo');
            $table->boolean('data_authorization')->default(false);
            $table->timestamp('authorization_date')->nullable();
            $table->string('political_version')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guardians');
    }
};
