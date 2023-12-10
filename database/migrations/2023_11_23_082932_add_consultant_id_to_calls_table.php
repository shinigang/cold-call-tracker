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
        Schema::table('calls', function (Blueprint $table) {
            $table->after('appointment_at', function (Blueprint $table) {
                $table->unsignedBigInteger('consultant_id')->nullable();
                $table->foreign('consultant_id')->references('id')->on('users')->onDelete('set null');

                $table->string('meeting_email')->nullable();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calls', function (Blueprint $table) {
            $table->dropColumn('consultant_id');
            $table->dropColumn('meeting_email');
        });
    }
};
