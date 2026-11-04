<?php

namespace App\Blocks;

use Mainstay\Content\Block;
use Mainstay\Content\Description;
use Mainstay\Content\Example;
use Mainstay\Content\Icon;
use Mainstay\Fields\Blocks;
use Mainstay\Fields\Select;
use Mainstay\Fields\Text;
use Mainstay\Fields\Textarea;

/* The opening of a page: a line naming it, its heading, a lead, buttons, and
   a code sample beside them where one is given. */
#[Icon('banner')]
#[Description('The opening of a page: its heading and lead, buttons under them, and a code sample beside them.')]
#[Example(['eyebrow' => 'A CMS for Laravel', 'heading' => 'Content modelled in PHP', 'lede' => 'Content types are PHP classes and most of their fields are real columns.'], inner: [Button::class])]
#[Blocks([Button::class, Command::class, Note::class])]
class Hero extends Block
{
    /* The short line over the heading, often the page's own name. */
    #[Text]
    public ?string $eyebrow;

    /* Each line drawn on a line of its own. */
    #[Textarea(required: true)]
    public string $heading;

    #[Textarea]
    public ?string $lede;

    /* A line of where things stand, under the buttons. */
    #[Text]
    public ?string $status;

    /* The code sample beside the text, under the name of its file. */
    #[Text]
    public ?string $caption;

    #[Textarea]
    public ?string $code;

    #[Select(options: ['stacked' => 'Heading over the lead', 'split' => 'Heading beside a narrow lead', 'halves' => 'Heading and lead in two halves'])]
    public string $layout = 'stacked';
}
