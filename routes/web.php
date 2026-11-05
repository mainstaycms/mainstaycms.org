<?php

use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

/*
 | Every public page is an entry, which Mainstay's catch-all serves at the
 | path its type's #[Route] builds. What is here is what no entry is.
 */
Route::get('sitemap.xml', SitemapController::class);
