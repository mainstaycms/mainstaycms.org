<?php

namespace App\Blocks;

use App\Content\Page;
use Mainstay\Content\Block;
use Mainstay\Content\Description;
use Mainstay\Content\Example;
use Mainstay\Content\Icon;
use Mainstay\Fields\Relation;
use Mainstay\Fields\Textarea;

/* How far the plan has come, counted from its phases when the page is
   drawn, as a card leading to the roadmap. */
#[Icon('calendar')]
#[Description('How far the plan has come, counted from its phases, leading to the roadmap.')]
#[Example(['text' => 'From declaring a content type to its API.'])]
class RoadmapCard extends Block
{
    #[Relation(to: Page::class)]
    public ?Page $page;

    #[Textarea]
    public ?string $text;
}
