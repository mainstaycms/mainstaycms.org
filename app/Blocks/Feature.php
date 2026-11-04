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
use Mainstay\Fields\Text;
use Mainstay\Fields\Textarea;

/* One feature, told in full: its number, heading and text, a link to more,
   and what shows it under them. */
#[Icon('star')]
#[Description('One feature: a heading and text with a link to more, over what shows it.')]
#[Example(['label' => 'Real columns', 'heading' => 'Real columns, kept in step', 'text' => 'Every type gets a table of its own.'])]
#[Blocks([Columns::class, CodeSample::class, CodeTabs::class, Terminal::class, Image::class, Steps::class, Tiles::class, Badges::class, QueryDiagram::class, LocaleDemo::class, DepthTree::class, Lifecycle::class])]
class Feature extends Block
{
    /* Its name in the list at the side. */
    #[Text(required: true)]
    public string $label;

    /* Its id, which a link to it ends with after the #. */
    #[Text]
    public ?string $anchor;

    #[Text(required: true)]
    public string $heading;

    #[Textarea]
    public ?string $text;

    #[Text]
    public ?string $linkLabel;

    #[Relation(to: [Page::class, Doc::class])]
    public Page|Doc|null $link;
}
