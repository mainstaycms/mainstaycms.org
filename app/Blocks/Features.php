<?php

namespace App\Blocks;

use Mainstay\Content\Block;
use Mainstay\Content\Description;
use Mainstay\Content\Example;
use Mainstay\Content\Icon;
use Mainstay\Fields\Blocks;

/* Everything Mainstay does, a numbered part each, with a list of them at the
   side that stays in view and leads to each. */
#[Icon('list')]
#[Description('Numbered parts, each a feature told in full, with a list at the side leading to each.')]
#[Example(inner: [Feature::class])]
#[Blocks([Feature::class])]
class Features extends Block {}
