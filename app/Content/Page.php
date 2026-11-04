<?php

namespace App\Content;

use App\Blocks\ArticleList;
use App\Blocks\Chapters;
use App\Blocks\Cta;
use App\Blocks\DocsList;
use App\Blocks\Features;
use App\Blocks\Hero;
use App\Blocks\Image;
use App\Blocks\Phases;
use App\Blocks\PluginGrid;
use App\Blocks\Progress;
use App\Blocks\Section;
use Illuminate\Support\Collection;
use Mainstay\Content\Entry;
use Mainstay\Content\Route;
use Mainstay\Facades\Mainstay;
use Mainstay\Fields\Blocks;
use Mainstay\Fields\Relation;
use Mainstay\Fields\Text;
use Mainstay\Fields\Textarea;

/*
 | A page at the top level, every page there is: the front page, the blog,
 | the documentation and the rest are edited alike. What a page is for is the
 | blocks its content holds -- a list of articles makes it the blog -- and
 | which one is the front page is the site's setting.
 */
#[Route('/{slug}')]
#[Blocks([Hero::class, Section::class, Image::class, Features::class, Chapters::class, Progress::class, Phases::class, PluginGrid::class, ArticleList::class, DocsList::class, Cta::class])]
class Page extends Entry
{
    #[Text(required: true, localized: true)]
    public string $slug;

    #[Textarea(localized: true)]
    public ?string $summary;

    /* The page this one tells more of, linked above it and marked in the
       menu while it is shown. */
    #[Relation(to: Page::class)]
    public ?Page $parent;

    /*
     | The page holding a block of this kind -- the blog is the page listing
     | the articles -- found by what it holds, since its slug is an editor's
     | to change. A block inside another counts as held.
     */
    public static function holding(string $block): ?self
    {
        return self::pages()->first(fn (self $page) => self::holds($page->content?->blocks() ?? [], $block));
    }

    /* Every page, read once a request -- Octane forgets once() between
       requests, as it would not a static. */
    private static function pages(): Collection
    {
        return once(fn () => Mainstay::find(self::class, depth: 0));
    }

    private static function holds(array $blocks, string $block): bool
    {
        return collect($blocks)->contains(fn (object $held) => $held instanceof $block || self::holds($held->inner(), $block));
    }

    /* Whether this is the site's front page, which one not yet published is
       not. */
    public function isFront(): bool
    {
        return isset($this->id) && Mainstay::frontPage() === [self::class, $this->id];
    }
}
