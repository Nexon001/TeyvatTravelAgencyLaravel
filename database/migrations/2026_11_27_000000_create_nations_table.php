<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // NATIONS in the concept paper: the seven fixed nations. Seeded by NationSeeder (from resources/data/nations.php),
        // never added or removed through the admin side. The paper's table has no created_at/updated_at.
        Schema::create('nations', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 50)->unique();
            $table->string('nation_name', 100);
            $table->text('description');
            $table->string('hero_image', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nations');
    }
};
