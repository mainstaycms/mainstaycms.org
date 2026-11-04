<?php

namespace App\Blocks;

use Mainstay\Content\Block;
use Mainstay\Content\Description;
use Mainstay\Content\Example;
use Mainstay\Content\Icon;
use Mainstay\Fields\Text;

/* A command to run, on a line of its own after a prompt, with a button that
   copies it. */
#[Icon('code')]
#[Description('A command on one line, after a prompt, with a button copying it.')]
#[Example(['command' => 'composer require mainstay/cms'])]
class Command extends Block
{
    #[Text(required: true)]
    public string $command;
}
