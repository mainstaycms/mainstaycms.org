<?php

namespace App\Blocks;

use Mainstay\Content\Block;
use Mainstay\Content\Description;
use Mainstay\Content\Example;
use Mainstay\Content\Icon;
use Mainstay\Fields\Text;

/* A name and its value. */
#[Icon('block')]
#[Description('A name and its value.')]
#[Example(['label' => 'PHP', 'value' => '8.3 or newer'])]
class Tile extends Block
{
    #[Text(required: true)]
    public string $label;

    #[Text]
    public ?string $value;
}
