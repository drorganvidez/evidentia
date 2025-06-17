<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuration', function (Blueprint $table) {
            $table->id();
            $table->timestamp('upload_evidences_timestamp')->nullable();
            $table->timestamp('validate_evidences_timestamp')->nullable();
            $table->timestamp('meetings_timestamp')->nullable();
            $table->timestamp('bonus_timestamp')->nullable();
            $table->timestamp('attendee_timestamp')->nullable();
            $table->string('eventbrite_token')->nullable();
            $table->timestamp('events_uploaded_timestamp')->nullable();
            $table->timestamp('attendees_uploaded_timestamp')->nullable();
            $table->string('secret')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuration');
    }
};
