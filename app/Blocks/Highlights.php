<?php

namespace App\Blocks;

use Mainstay\Content\Block;
use Mainstay\Content\Description;
use Mainstay\Content\Example;
use Mainstay\Content\Icon;
use Mainstay\Fields\Blocks;

/* What Mainstay does, a numbered row each, every row leading to the part of
   a page that tells it in full. */
#[Icon('list')]
#[Description('Numbered rows, each a title and a line, leading to where they are told in full.')]
#[Example(inner: [Highlight::class, Highlight::class])]
#[Blocks([Highlight::class])]
class Highlights extends Block {}
