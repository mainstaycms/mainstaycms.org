<?php

namespace App\Blocks;

use Mainstay\Content\Block;
use Mainstay\Content\Description;
use Mainstay\Content\Example;
use Mainstay\Content\Icon;
use Mainstay\Fields\Blocks;
use Mainstay\Fields\Select;

/* Cards in a grid: three across, or small tiles six across. */
#[Icon('grid')]
#[Description('Cards in a grid, three across, or small tiles six across.')]
#[Example(inner: [Card::class, Card::class, Card::class])]
#[Blocks([Card::class])]
class Cards extends Block
{
    #[Select(options: ['cards' => 'Cards', 'tiles' => 'Small tiles'])]
    public string $size = 'cards';
}
