<?php

namespace App\Content;

use Mainstay\Content\Route;
use Mainstay\Content\Taxonomy;
use Mainstay\Fields\Text;

/* What an article is about. Each tag's page sits under the blog, as one of
   its filters, and lists the articles holding it. */
#[Route('/blog/tag/{slug}')]
class Tag extends Taxonomy
{
    #[Text(required: true, localized: true)]
    public string $slug;
}
