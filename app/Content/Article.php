<?php

namespace App\Content;

use App\Blocks\CodeSample;
use App\Blocks\CodeTabs;
use App\Blocks\Image;
use App\Blocks\Note;
use App\Blocks\Tiles;
use Carbon\CarbonImmutable;
use Mainstay\Content\Entry;
use Mainstay\Content\Route;
use Mainstay\Fields\Blocks;
use Mainstay\Fields\Date;
use Mainstay\Fields\Relation;
use Mainstay\Fields\Terms;
use Mainstay\Fields\Text;
use Mainstay\Fields\Textarea;

#[Route('/blog/{slug}')]
#[Blocks([CodeSample::class, CodeTabs::class, Tiles::class, Note::class, Image::class])]
class Article extends Entry
{
    #[Text(required: true, localized: true)]
    public string $slug;

    #[Textarea(required: true, localized: true)]
    public string $summary;

    #[Date(required: true)]
    public CarbonImmutable $publishedOn;

    /* A few characters of code standing for the article on its cover, where
       it is listed and above it, in place of a picture. */
    #[Text(max: 40)]
    public ?string $glyph;

    /* The first is the one it is filed under: named above its title and in
       its address back to the blog. */
    #[Terms(of: Tag::class)]
    public array $tags;

    /* The entry in Mainstay's decisions log the article tells, linked at its
       end. */
    #[Text(max: 500)]
    public ?string $decision;

    /* Drawn under the content, before the newest of the rest. */
    #[Relation(to: Article::class)]
    public array $related;
}
