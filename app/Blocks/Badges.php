<?php

namespace App\Blocks;

use Mainstay\Content\Block;
use Mainstay\Content\Description;
use Mainstay\Content\Example;
use Mainstay\Content\Icon;
use Mainstay\Fields\Textarea;

/* Short labels in a row, one a line. */
#[Icon('list')]
#[Description('Short labels in a row, each a line of its text.')]
#[Example(['items' => "card 640 · avif\ncard 640 · webp"])]
class Badges extends Block
{
    #[Textarea(required: true)]
    public string $items;

    /** @return list<string> */
    public function labels(): array
    {
        return array_values(array_filter(array_map(trim(...), preg_split('/\R/', $this->items))));
    }
}
