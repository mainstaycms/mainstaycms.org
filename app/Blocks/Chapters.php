<?php

namespace App\Blocks;

use Mainstay\Content\Block;
use Mainstay\Content\Description;
use Mainstay\Content\Example;
use Mainstay\Content\Icon;
use Mainstay\Fields\Blocks;

/* A story told in numbered steps, each its text beside what shows it, the
   sides changing from one to the next. */
#[Icon('ordered-list')]
#[Description('Numbered steps of a story, each its text beside a code sample or an image.')]
#[Example(inner: [Chapter::class])]
#[Blocks([Chapter::class])]
class Chapters extends Block {}
