<?php

namespace App\Blocks;

use Mainstay\Content\Block;
use Mainstay\Content\Description;
use Mainstay\Content\Icon;
use Mainstay\Fields\Blocks;

/* Images in a row running past the page's edge, moving along as the page
   scrolls. */
#[Icon('gallery')]
#[Description('Images in a row past the edge of the page, moving along as it scrolls.')]
#[Blocks([Image::class])]
class Gallery extends Block {}
