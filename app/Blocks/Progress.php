<?php

namespace App\Blocks;

use Mainstay\Content\Block;
use Mainstay\Content\Description;
use Mainstay\Content\Example;
use Mainstay\Content\Icon;
use Mainstay\Fields\Text;

/* The plan's phases as a bar, done ones filled, over what is done, what
   comes next and what it is tested on. */
#[Icon('calendar')]
#[Description('The plan\'s phases as a bar, over what is done, what is next and what it is tested on.')]
#[Example(['testedOn' => 'SQLite, PostgreSQL, MySQL, SQL Server'])]
class Progress extends Block
{
    #[Text]
    public ?string $testedOn;
}
