<?php

namespace App\Blocks;

use Mainstay\Content\Block;
use Mainstay\Content\Description;
use Mainstay\Content\Example;
use Mainstay\Content\Icon;
use Mainstay\Fields\Blocks;
use Mainstay\Fields\Boolean;

/* Steps in their order, each numbered, in a panel or on the page. */
#[Icon('ordered-list')]
#[Description('Numbered steps, each a title and a line, in a panel or on the page.')]
#[Example(inner: [Step::class, Step::class])]
#[Blocks([Step::class])]
class Steps extends Block
{
    #[Boolean]
    public bool $panel = true;
}
