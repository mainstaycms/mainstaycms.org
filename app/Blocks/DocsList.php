<?php

namespace App\Blocks;

use Mainstay\Content\Block;
use Mainstay\Content\Description;
use Mainstay\Content\Example;
use Mainstay\Content\Icon;
use Mainstay\Fields\Blocks;

/* The documentation by section, a card each, listing its docs in reading
   order: what makes a page the documentation's index. */
#[Icon('book')]
#[Description('The documentation by section, a card each listing its docs: what makes a page its index.')]
#[Example(inner: [DocsSection::class])]
#[Blocks([DocsSection::class])]
class DocsList extends Block {}
