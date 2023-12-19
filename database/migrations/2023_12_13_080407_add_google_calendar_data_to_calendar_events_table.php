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
        Schema::table('calendar_events', function (Blueprint $table) {
            $table->after('call_id', function (Blueprint $table) {
                $table->string('calendar_id')->nullable();
                $table->string('calendar_link')->nullable();
                $table->string('meet_link')->nullable();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calendar_events', function (Blueprint $table) {
            $table->dropColumns(['calendar_id', 'calendar_link', 'meet_link']);
        });
    }
};
