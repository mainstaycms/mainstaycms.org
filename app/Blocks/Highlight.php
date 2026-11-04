<?php

namespace App\Blocks;

use App\Content\Page;
use Mainstay\Content\Block;
use Mainstay\Content\Description;
use Mainstay\Content\Example;
use Mainstay\Content\Icon;
use Mainstay\Fields\Relation;
use Mainstay\Fields\Text;
use Mainstay\Fields\Textarea;

/* One row: a title and a line, leading to the part of a page that tells
   it in full. */
#[Icon('star')]
#[Description('One row: a title and a line under it, leading to a part of a page.')]
#[Example(['title' => 'Real columns', 'text' => 'Every type gets a table of its own.'])]
class Highlight extends Block
{
    #[Text(required: true)]
    public string $title;

    #[Textarea]
    public ?string $text;

    #[Relation(to: Page::class)]
    public ?Page $page;

    /* The id of the part of the page it leads to, without the #. */
    #[Text]
    public ?string $anchor;

    public function href(): ?string
    {
        if ($this->page === null || $this->page->missing) {
            return null;
        }

        return $this->page->url().(blank($this->anchor) ? '' : '#'.$this->anchor);
    }
}
