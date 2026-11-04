<?php

namespace App\Blocks;

use Mainstay\Content\Block;
use Mainstay\Content\Description;
use Mainstay\Content\Icon;

/* A change going from draft to published to a revision: a picture with nothing to fill in. */
#[Icon('image')]
#[Description('A change going from draft to published to a revision, drawn.')]
class Lifecycle extends Block {}
