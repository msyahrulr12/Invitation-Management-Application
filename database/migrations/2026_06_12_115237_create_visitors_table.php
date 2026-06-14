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
        Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->index();
            $table->string('code_uuid')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status');
            $table->string('presence_image')->nullable();
            $table->string('presence_image_url')->nullable();
            $table->string('presence_latitude')->nullable();
            $table->string('presence_longitude')->nullable();
            $table->timestamp('presence_timestamp')->nullable();
            $table->foreignId('receptionist_id')->nullable()->index();
            $table->string('receptionist_name')->nullable();
            $table->string('receptionist_code_uuid')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->foreignId('created_by')->nullable();
            $table->foreignId('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitors');
    }
};
