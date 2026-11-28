<?php

namespace Database\Seeders;

use App\Models\Nation;
use Illuminate\Database\Seeder;

/**
 * Fills the NATIONS table from resources/data/nations.php (the single source of nation names).
 * Safe to run again: rows are matched by slug and updated, never duplicated.
 * Run with:  php artisan db:seed --class=NationSeeder
 */
class NationSeeder extends Seeder
{
    public function run(): void
    {
        $nations = include resource_path('data/nations.php');

        foreach ($nations as $slug => $nation) {
            Nation::updateOrCreate(
                ['slug' => $slug],
                [
                    'nation_name' => $nation['name'],
                    'description' => $nation['lede'],
                    'hero_image' => 'images/nations/'.$slug.'-hero.jpg',
                ],
            );
        }
    }
}
