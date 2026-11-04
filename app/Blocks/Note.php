<?php

namespace App\Blocks;

use Mainstay\Content\Block;
use Mainstay\Content\Description;
use Mainstay\Content\Example;
use Mainstay\Content\Icon;
use Mainstay\Fields\Textarea;

/* A line the reader should not miss, set apart in the accent colour. */
#[Icon('quote')]
#[Description('A line the reader should not miss, set apart in the accent colour.')]
#[Example(['text' => 'Mainstay is not released yet.'])]
class Note extends Block
{
    #[Textarea(required: true)]
    public string $text;
}
