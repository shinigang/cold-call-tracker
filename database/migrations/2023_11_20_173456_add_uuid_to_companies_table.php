<?php

use App\Models\Company;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->after('id', function (Blueprint $table) {
                $table->uuid('uuid')->nullable()->unique();
            });
        });

        $companies = Company::all();
        foreach ($companies as $company) {
            $company->uuid = Str::uuid()->toString();
            $company->save();
        }

        Schema::table('companies', function (Blueprint $table) {
            $table->uuid('uuid')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
