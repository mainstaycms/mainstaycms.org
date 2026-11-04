<?php

namespace App\Blocks;

use Mainstay\Content\Block;
use Mainstay\Content\Description;
use Mainstay\Content\Example;
use Mainstay\Content\Icon;
use Mainstay\Fields\Blocks;

/* Plugins as cards, with a search over them and a filter by what they add,
   which the page's script brings. */
#[Icon('grid')]
#[Description('Plugins as cards, searched and filtered by what they add.')]
#[Example(inner: [PluginCard::class, PluginCard::class])]
#[Blocks([PluginCard::class])]
class PluginGrid extends Block {}
