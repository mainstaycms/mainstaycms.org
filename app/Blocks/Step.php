<?php

namespace App\Blocks;

use Mainstay\Content\Block;
use Mainstay\Content\Description;
use Mainstay\Content\Example;
use Mainstay\Content\Icon;
use Mainstay\Fields\Text;
use Mainstay\Fields\Textarea;

/* One step: a title, a line, and a badge beside them. */
#[Icon('ordered-list')]
#[Description('One step: a title, a line under it and a badge beside them.')]
#[Example(['title' => 'Your own routes', 'text' => 'Anything in routes/web.php answers first.', 'badge' => 'web.php'])]
class Step extends Block
{
    #[Text(required: true)]
    public string $title;

    #[Textarea]
    public ?string $text;

    #[Text]
    public ?string $badge;
}
