<?php

namespace App\Blocks;

use Mainstay\Content\Block;
use Mainstay\Content\Description;
use Mainstay\Content\Example;
use Mainstay\Content\Icon;
use Mainstay\Fields\Blocks;
use Mainstay\Fields\Text;
use Mainstay\Fields\Textarea;

/* The page's last word, in the accent colour: a heading, a line and a
   button, with code to run beside them. */
#[Icon('banner')]
#[Description('A closing panel in the accent colour: a heading, a line and a button, with code beside them.')]
#[Example(['heading' => 'Try it in a Laravel app', 'text' => 'Require the package and make the first account.'], inner: [Button::class])]
#[Blocks([Button::class])]
class Cta extends Block
{
    #[Text(required: true)]
    public string $heading;

    #[Textarea]
    public ?string $text;

    #[Text]
    public ?string $caption;

    #[Textarea]
    public ?string $code;
}
