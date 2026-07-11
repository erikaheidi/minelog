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
        Schema::create('waypoints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('world_id')->constrained()->cascadeOnDelete();
            $table->string('external_id')->nullable(); // per-waypoint id from the in-game add-on, for dedup
            $table->string('name')->nullable();
            $table->integer('x')->nullable();
            $table->integer('y')->nullable();
            $table->integer('z')->nullable();
            $table->string('dimension')->default('overworld'); // overworld|nether|end
            $table->text('note')->nullable();
            $table->json('tags')->nullable();
            $table->timestamp('captured_at')->nullable();
            $table->string('screenshot_path')->nullable(); // optional imagery, decoupled from coordinates
            $table->string('status')->default('draft'); // draft|confirmed
            $table->timestamps();

            $table->unique(['world_id', 'external_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('waypoints');
    }
};
