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
        Schema::create('call_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('status')->unique();
            $table->unsignedBigInteger('status_group_id');
            $table->boolean('system_default')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('status_group_id')->references('id')->on('call_status_groups');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('call_statuses');
    }
};
