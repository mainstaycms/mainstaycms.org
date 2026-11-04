<?php

namespace App\Blocks;

use Mainstay\Content\Block;
use Mainstay\Content\Description;
use Mainstay\Content\Example;
use Mainstay\Content\Icon;
use Mainstay\Fields\Select;

/* The columns one of this site's types is stored in, read from the database
   as it is when the page is drawn: a column per field, in its table. */
#[Icon('table')]
#[Description('The columns one of this site\'s types is stored in, read from the database.')]
#[Example(['of' => 'article'])]
class TableColumns extends Block
{
    #[Select(options: ['article' => 'Articles', 'page' => 'Pages', 'doc' => 'Docs', 'phase' => 'Phases'], required: true)]
    public string $of = 'article';
}
