<?php

namespace App\Blocks;

use Mainstay\Content\Block;
use Mainstay\Content\Description;
use Mainstay\Content\Icon;

/* An article and what a read follows from it, depth by depth: a picture with nothing to fill in. */
#[Icon('image')]
#[Description('An article and what a read follows from it, depth by depth, drawn.')]
class DepthTree extends Block {}
