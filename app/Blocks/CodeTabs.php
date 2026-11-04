<?php

namespace App\Blocks;

use Mainstay\Content\Block;
use Mainstay\Content\Description;
use Mainstay\Content\Example;
use Mainstay\Content\Icon;
use Mainstay\Fields\Blocks;

/* Code samples that belong together, one at a time, each under a tab named
   after its file. */
#[Icon('code')]
#[Description('Code samples that belong together, one at a time under a tab named after each file.')]
#[Example(inner: [CodeSample::class, CodeSample::class])]
#[Blocks([CodeSample::class])]
class CodeTabs extends Block {}
