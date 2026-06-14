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
        Schema::create('event_receptionist', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('receptionist_id')->constrained()->cascadeOnDelete();
            $table->string('code_uuid')->unique();
            $table->string('pin', 6); // PIN for receptionist scanner auth
            $table->timestamps();

            $table->unique(['event_id', 'receptionist_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_receptionist');
    }
};
