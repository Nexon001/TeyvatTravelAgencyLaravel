<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Nation slug (e.g. "liyue"). "itinerary" already exists: it holds an id like "liyue-1", or null for a custom route.
            $table->string('nation', 30)->nullable()->after('phone');
            // Title copied at booking time, so old bookings stay readable if the itinerary data changes.
            $table->string('itinerary_title', 160)->nullable()->after('itinerary');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['nation', 'itinerary_title']);
        });
    }
};
