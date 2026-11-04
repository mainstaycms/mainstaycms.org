<?php

namespace App\Blocks;

use Mainstay\Content\Block;
use Mainstay\Content\Description;
use Mainstay\Content\Example;
use Mainstay\Content\Icon;
use Mainstay\Fields\Text;
use Mainstay\Fields\Textarea;

/* A command and what it printed, a line at a time as it comes into view: a
   line starting with ✓ is a pass, with ✗ a failure. */
#[Icon('code')]
#[Description('A command and what it printed, appearing a line at a time.')]
#[Example(['command' => 'php artisan mainstay:schema:check', 'output' => 'The database matches the declared content types.'])]
class Terminal extends Block
{
    #[Text(required: true)]
    public string $command;

    #[Textarea]
    public ?string $output;
}
