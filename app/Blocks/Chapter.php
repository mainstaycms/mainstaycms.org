<?php

namespace App\Blocks;

use Mainstay\Content\Block;
use Mainstay\Content\Description;
use Mainstay\Content\Example;
use Mainstay\Content\Icon;
use Mainstay\Fields\Blocks;
use Mainstay\Fields\Text;
use Mainstay\Fields\Textarea;

/* One step of a story: its heading and text, and the code samples and
   images beside them. */
#[Icon('block')]
#[Description('One step: a heading and text, beside code samples and images.')]
#[Example(['heading' => 'A block is a PHP class', 'text' => 'Fields on a block are declared as they are on a type.'])]
#[Blocks([CodeSample::class, Image::class])]
class Chapter extends Block
{
    #[Text(required: true)]
    public string $heading;

    #[Textarea]
    public ?string $text;
}
