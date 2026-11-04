<?php

namespace App\Blocks;

use Mainstay\Content\Block;
use Mainstay\Content\Description;
use Mainstay\Content\Example;
use Mainstay\Content\Icon;
use Mainstay\Fields\Blocks;
use Mainstay\Fields\Select;
use Mainstay\Fields\Text;

/* Pairs of a name and a value: as tiles, as rows in a panel under a
   heading, or as requests and what answers them. */
#[Icon('grid')]
#[Description('Pairs of a name and a value, as tiles, as rows in a panel, or as routes.')]
#[Example(inner: [Tile::class, Tile::class])]
#[Blocks([Tile::class])]
class Tiles extends Block
{
    /* Over the rows. */
    #[Text]
    public ?string $heading;

    #[Select(options: ['grid' => 'Tiles', 'rows' => 'Rows in a panel', 'routes' => 'Routes'])]
    public string $style = 'grid';
}
