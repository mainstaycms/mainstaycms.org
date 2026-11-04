<?php

namespace App\Content;

use App\Blocks\CodeSample;
use App\Blocks\CodeTabs;
use App\Blocks\Image;
use App\Blocks\Note;
use App\Blocks\Tiles;
use Mainstay\Content\Entry;
use Mainstay\Content\Route;
use Mainstay\Content\Template;
use Mainstay\Fields\Blocks;
use Mainstay\Fields\Number;
use Mainstay\Fields\Select;
use Mainstay\Fields\Text;
use Mainstay\Fields\Textarea;

#[Route('/docs/{slug}')]
#[Template('docs.page')]
#[Blocks([CodeSample::class, CodeTabs::class, Tiles::class, Note::class, Image::class])]
class Doc extends Entry
{
    /* In reading order, each the heading of its docs in the side menu and a
       card on the documentation's page. */
    public const SECTIONS = [
        'start' => 'Getting started',
        'content' => 'Content',
        'relations' => 'Relations',
        'rendering' => 'Rendering',
        'editing' => 'Editing',
        'extending' => 'Extending',
    ];

    #[Text(required: true, localized: true)]
    public string $slug;

    #[Select(options: self::SECTIONS, required: true)]
    public string $section;

    /* Its place in its section. */
    #[Number(required: true)]
    public int $position;

    #[Textarea(localized: true)]
    public ?string $summary;
}
