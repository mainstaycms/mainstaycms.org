<?php

namespace App\Blocks;

use Mainstay\Content\Block;
use Mainstay\Content\Description;
use Mainstay\Content\Example;
use Mainstay\Content\Icon;
use Mainstay\Fields\Blocks;
use Mainstay\Fields\Text;

/* One tab beside a code sample: under its label, a heading with a badge, and
   text and blocks under it. */
#[Icon('block')]
#[Description('One tab: a heading with a badge, over text and blocks.')]
#[Example(['label' => 'Columns', 'heading' => 'Tables with real columns'], inner: [['p' => 'Translated fields live in a table of their own.']])]
#[Blocks([TableColumns::class, Image::class, Tiles::class, CodeSample::class], text: true)]
class Tab extends Block
{
    #[Text(required: true)]
    public string $label;

    #[Text(required: true)]
    public string $heading;

    #[Text]
    public ?string $badge;
}
