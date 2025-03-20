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
        Schema::create('prayer_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });

        // add prayer_type_id to prayers table
        Schema::table('prayers', function (Blueprint $table) {
            $table->unsignedSmallInteger('prayer_type_id')->after('content');
        });

        DB::table('prayer_types')->insert([
            ['name' => 'Adoration'],
            ['name' => 'Confession'],
            ['name' => 'Thanksgiving'],
            ['name' => 'Supplication']
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prayer_types');
    }
};
