<?php

namespace App\Blocks;

use Mainstay\Content\Block;
use Mainstay\Content\Description;
use Mainstay\Content\Example;
use Mainstay\Content\Icon;
use Mainstay\Fields\Blocks;

/* Columns side by side, as wide as each other, one under another where the
   page is narrow. */
#[Icon('columns')]
#[Description('Columns side by side, each holding text and blocks.')]
#[Example(inner: [Column::class, Column::class])]
#[Blocks([Column::class])]
class Columns extends Block {}
