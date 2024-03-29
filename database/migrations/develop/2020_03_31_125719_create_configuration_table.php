<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CreateConfigurationTable extends Migration
{
    protected $connection = 'base21';
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('configuration', function (Blueprint $table) {
            $table->id();

            // límites
            $table->integer('max_attachment_number')->default(-1);
            $table->integer('max_attachment_size')->default(-1);
            $table->integer('max_proof_number')->default(-1);
            $table->integer('max_proof_size')->default(-1);
            $table->integer('max_evidence_number')->default(-1);
            $table->integer('max_assist_number')->default(-1);
            $table->integer('max_attendees_hours')->default(10);

            // límites de fechas
            $table->timestamp('upload_evidences_timestamp', 0)->default(\Carbon\Carbon::now()->addDays(120)->toDateTimeString())->nullable();
            $table->timestamp('validate_evidences_timestamp', 0)->default(\Carbon\Carbon::now()->addDays(120)->toDateTimeString())->nullable();
            $table->timestamp('meetings_timestamp', 0)->default(\Carbon\Carbon::now()->addDays(120)->toDateTimeString())->nullable();
            $table->timestamp('bonus_timestamp', 0)->default(\Carbon\Carbon::now()->addDays(120)->toDateTimeString())->nullable();
            $table->timestamp('attendee_timestamp', 0)->default(\Carbon\Carbon::now()->addDays(120)->toDateTimeString())->nullable();

            // actualizaciones
            $table->timestamp('events_uploaded_timestamp', 0)->nullable();
            $table->timestamp('attendees_uploaded_timestamp', 0)->nullable();

            // otros
            $table->string('secret')->default(Str::random(10));
            $table->string('eventbrite_token')->default('LMMJ22DGZJL3YGV5AVGC');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('configuration');
    }
}
