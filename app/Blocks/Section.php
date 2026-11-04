<?php

namespace App\Blocks;

use App\Content\Doc;
use App\Content\Page;
use Mainstay\Content\Block;
use Mainstay\Content\Description;
use Mainstay\Content\Example;
use Mainstay\Content\Icon;
use Mainstay\Fields\Blocks;
use Mainstay\Fields\Relation;
use Mainstay\Fields\Select;
use Mainstay\Fields\Text;
use Mainstay\Fields\Textarea;

/* A part of a page under a heading, with a lead and a link beside it, and
   what it shows under them -- or, laid out at the side, beside them. */
#[Icon('columns')]
#[Description('A part of a page: a heading with a lead and a link, over what it shows.')]
#[Example(['heading' => 'The page builder', 'lede' => 'Editors work on the page as your site draws it.'])]
#[Blocks([Image::class, Gallery::class, Tabs::class, Highlights::class, RoadmapCard::class, Cards::class, Columns::class, CodeSample::class, Steps::class, Commands::class, DocsList::class, Buttons::class, Badges::class])]
class Section extends Block
{
    #[Text(required: true)]
    public string $heading;

    #[Textarea]
    public ?string $lede;

    /* Under the lead, or a quieter button in its place where there is none. */
    #[Text]
    public ?string $linkLabel;

    #[Relation(to: [Page::class, Doc::class])]
    public Page|Doc|null $link;

    #[Select(options: ['top' => 'Heading over what it shows', 'side' => 'Heading beside what it shows'])]
    public string $layout = 'top';
}
