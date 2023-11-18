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
        Schema::create('calls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('contact_number');
            $table->dateTime('called_at', $precision = 0);
            $table->string('status');
            // ['Set Appointment Date', 'Call Again on Date', 'Not Reached', 'Not Interested', 'Does not want to be called anymore', 'Happy with website']
            $table->dateTime('follow_up_at', $precision = 0)->nullable();
            $table->dateTime('appointment_at', $precision = 0)->nullable();

            // LEGEND
            // - Set Appointment = Successful calls [Green]
            // - Call Again on Date = Follow-Up calls [Blue]
            // - Not Reached / Not Interested / Does not want to be called anymore / Happy with website = Other Status [Grey]

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calls');
    }
};
