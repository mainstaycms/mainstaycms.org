<?php

namespace App\Blocks;

use App\Content\Doc;
use Mainstay\Content\Block;
use Mainstay\Content\Description;
use Mainstay\Content\Example;
use Mainstay\Content\Icon;
use Mainstay\Fields\Select;
use Mainstay\Fields\Textarea;

/* One section of the documentation: what it covers, and its docs. */
#[Icon('book')]
#[Description('One section of the documentation: what it covers, and its docs in reading order.')]
#[Example(['section' => 'start', 'text' => 'Install the package and render a first page.'])]
class DocsSection extends Block
{
    /* Each section's icon on its card. */
    public const SYMBOLS = [
        'start' => Symbol::Arrow,
        'content' => Symbol::Lines,
        'relations' => Symbol::Nodes,
        'rendering' => Symbol::Window,
        'editing' => Symbol::Pencil,
        'extending' => Symbol::Plug,
    ];

    #[Select(options: Doc::SECTIONS, required: true)]
    public string $section = 'start';

    #[Textarea]
    public ?string $text;
}
