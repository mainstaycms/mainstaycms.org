<?php

namespace App\Blocks;

use Mainstay\Content\Block;
use Mainstay\Content\Description;
use Mainstay\Content\Example;
use Mainstay\Content\Icon;
use Mainstay\Fields\Blocks;

/* What one column holds, one under another. */
#[Icon('columns')]
#[Description('One column: text and blocks, one under another.')]
#[Example(inner: [['h3' => 'Declared in code'], ['p' => 'A content type is a class.']])]
#[Blocks([CodeSample::class, CodeTabs::class, Image::class, Tiles::class, Terminal::class, Steps::class, Badges::class, Note::class, Buttons::class, QueryDiagram::class, LocaleDemo::class, DepthTree::class, Lifecycle::class], text: true)]
class Column extends Block {}
