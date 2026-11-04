<?php

namespace Database\Seeders;

use App\Content\Article;
use Illuminate\Database\Seeder;
use Mainstay\Auth\Capabilities;
use Mainstay\Auth\Role;
use Mainstay\Auth\User;

/*
 | Who signs in to the admin. A Writer may write and publish articles and
 | nothing else, which is a role a site makes from its own types. The
 | administrator comes from .env, when it names one, so a rebuild of the
 | content leaves a way in; the seeder holds no password of its own.
 */
class AccountSeeder extends Seeder
{
    public function run(): void
    {
        Role::query()->create(['name' => 'writer', 'capabilities' => Capabilities::of(Article::class)]);

        if (filled(env('MAINSTAY_ADMIN_EMAIL')) && filled(env('MAINSTAY_ADMIN_PASSWORD'))) {
            User::query()->create([
                'name' => env('MAINSTAY_ADMIN_NAME', 'Administrator'),
                'email' => env('MAINSTAY_ADMIN_EMAIL'),
                'password' => env('MAINSTAY_ADMIN_PASSWORD'),
                'role_id' => Role::query()->where('name', 'administrator')->value('id'),
            ]);
        }
    }
}
