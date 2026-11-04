<?php

namespace App\Blocks;

use Mainstay\Content\Block;
use Mainstay\Content\Description;
use Mainstay\Content\Icon;
use Mainstay\Content\Media;
use Mainstay\Fields\Blocks;
use Mainstay\Fields\Image as ImageField;
use Mainstay\Fields\Select;
use Mainstay\Fields\Text;

/* An image from the library in a frame, as wide as what it is in, with a
   caption under it and pins naming its parts. Tilted back, it straightens
   as it scrolls into view. */
#[Icon('image')]
#[Description('An image from the library in a frame, with a caption and pins naming its parts.')]
#[Blocks([Pin::class])]
class Image extends Block
{
    /* Screenshots are taken at twice the size they are shown. */
    #[ImageField(sizes: ['wide' => [2400]])]
    public ?Media $image;

    #[Text]
    public ?string $caption;

    #[Select(options: ['frame' => 'In a frame', 'tilt' => 'In a frame, tilted back', 'plain' => 'Without a frame'])]
    public string $style = 'frame';
}
