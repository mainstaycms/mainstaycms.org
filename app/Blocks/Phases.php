<?php

namespace App\Blocks;

use Mainstay\Content\Block;
use Mainstay\Content\Description;
use Mainstay\Content\Example;
use Mainstay\Content\Icon;
use Mainstay\Fields\Select;
use Mainstay\Fields\Text;
use Mainstay\Fields\Textarea;

/* The phases done, newest first, or the ones to come, in their order, on a
   line beside a heading. */
#[Icon('calendar')]
#[Description('The phases done, newest first, or those to come, on a line beside a heading.')]
#[Example(['heading' => 'Next', 'text' => 'In the order they are planned.'])]
class Phases extends Block
{
    #[Select(options: ['next' => 'The phases to come', 'done' => 'The phases done'], required: true)]
    public string $showing = 'next';

    #[Text(required: true)]
    public string $heading;

    #[Textarea]
    public ?string $text;
}
