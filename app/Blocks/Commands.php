<?php

namespace App\Blocks;

use Mainstay\Content\Block;
use Mainstay\Content\Description;
use Mainstay\Content\Example;
use Mainstay\Content\Icon;
use Mainstay\Fields\Blocks;

/* Artisan commands in a table, each with what it does and a button that
   copies it. */
#[Icon('table')]
#[Description('Artisan commands, each with what it does, when it is run and a Copy button.')]
#[Example(inner: [CommandRow::class, CommandRow::class])]
#[Blocks([CommandRow::class])]
class Commands extends Block {}
