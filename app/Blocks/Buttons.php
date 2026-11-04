<?php

namespace App\Blocks;

use Mainstay\Content\Block;
use Mainstay\Content\Description;
use Mainstay\Content\Example;
use Mainstay\Content\Icon;
use Mainstay\Fields\Blocks;

/* Buttons in a row. */
#[Icon('button')]
#[Description('Buttons in a row, each one a button inside it.')]
#[Example(inner: [Button::class])]
#[Blocks([Button::class])]
class Buttons extends Block {}
