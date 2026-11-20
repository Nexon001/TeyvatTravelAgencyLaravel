<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 12)->unique();
            $table->string('name');
            $table->string('email');
            $table->string('phone', 40);
            // Stored as Y-m-d strings so date comparisons stay exact on SQLite.
            $table->date('check_in');
            $table->date('check_out');
            $table->unsignedTinyInteger('days');
            $table->boolean('is_custom')->default(false);
            $table->string('status', 20)->default('confirmed'); // confirmed | pending | cancelled
            $table->string('itinerary', 80)->nullable();
            $table->timestamps();

            $table->index(['check_in', 'check_out']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
