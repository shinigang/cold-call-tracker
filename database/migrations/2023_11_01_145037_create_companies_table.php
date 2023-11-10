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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('industry')->nullable();
            $table->integer('total_employees')->nullable();
            // Status: ['Lead', 'Opportunity', 'Customer', 'Close']
            $table->string('status')->default('Lead');
            // Call Status: ["Unprocessed", "Not Reached", "Not Interested", "Doesn't want to be called anymore", "Call again on Date", "Set Appointment Date"]
            $table->string('call_status')->default('Unprocessed');
            $table->dateTime('follow_up_date')->nullable();
            $table->dateTime('appointment_date')->nullable();

            $table->string('email')->nullable();
            $table->string('referral_source')->nullable();
            $table->string('website')->nullable();
            $table->string('website_status')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('address_street')->nullable();
            $table->string('address_city')->nullable();
            $table->string('address_state')->nullable();
            $table->string('address_country')->nullable();
            $table->string('address_zipcode')->nullable();
            $table->boolean('verified_address')->default(0);
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('modified_by')->nullable();
            $table->unsignedBigInteger('assigned_caller')->nullable();
            $table->unsignedBigInteger('assigned_consultant')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('modified_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('assigned_caller')->references('id')->on('users')->onDelete('set null');
            $table->foreign('assigned_consultant')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
