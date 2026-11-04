<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/*
 | The site's content. It expects empty content tables -- `composer
 | content` rebuilds them first -- and writes the pages last, as they lead
 | to the docs, the articles and the roadmap's phases.
 */
class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoadmapSeeder::class,
            DocSeeder::class,
            ArticleSeeder::class,
            PageSeeder::class,
        ]);
    }
}
