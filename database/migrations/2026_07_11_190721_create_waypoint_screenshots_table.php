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
        Schema::create('waypoint_screenshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('waypoint_id')->constrained()->cascadeOnDelete();
            $table->string('disk');
            $table->string('path');
            $table->timestamps();
        });

        Schema::table('waypoints', function (Blueprint $table) {
            $table->dropColumn('screenshot_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('waypoints', function (Blueprint $table) {
            $table->string('screenshot_path')->nullable();
        });

        Schema::dropIfExists('waypoint_screenshots');
    }
};
