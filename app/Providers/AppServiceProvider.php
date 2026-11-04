<?php

namespace App\Providers;

use App\Content\Article;
use App\Content\Doc;
use App\Content\Layout;
use App\Content\Page;
use App\Content\Phase;
use App\Content\Tag;
use Illuminate\Support\ServiceProvider;
use Mainstay\Facades\Mainstay;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Mainstay::types([Page::class, Article::class, Tag::class, Doc::class, Phase::class, Layout::class]);
        Mainstay::editorStyles(['/css/site.css'], class: 'prose');
    }
}
