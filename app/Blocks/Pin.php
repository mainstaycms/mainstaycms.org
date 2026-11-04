<?php

namespace App\Blocks;

use Mainstay\Content\Block;
use Mainstay\Content\Description;
use Mainstay\Content\Example;
use Mainstay\Content\Icon;
use Mainstay\Fields\Number;
use Mainstay\Fields\Text;

/* A numbered label on an image, at a place given in hundredths of its width
   and height. Not drawn where the page is narrow. */
#[Icon('pin')]
#[Description('A numbered label on an image, placed by how far across and down it is.')]
#[Example(['label' => 'Block toolbar', 'x' => 20, 'y' => 20])]
class Pin extends Block
{
    #[Text(required: true)]
    public string $label;

    #[Number(min: 0, max: 100)]
    public int $x = 0;

    #[Number(min: 0, max: 100)]
    public int $y = 0;
}
