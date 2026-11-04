<?php

namespace App\Blocks;

use App\Content\Doc;
use App\Content\Page;
use Mainstay\Content\Block;
use Mainstay\Content\Description;
use Mainstay\Content\Example;
use Mainstay\Content\Icon;
use Mainstay\Fields\Relation;
use Mainstay\Fields\Select;
use Mainstay\Fields\Text;
use Mainstay\Fields\Textarea;

/* A title and a line, under an icon, leading somewhere when it names where. */
#[Icon('block')]
#[Description('A title and a line under an icon, leading to a page or an address.')]
#[Example(['title' => 'A plan, in phases', 'text' => 'Each phase says what it builds.'])]
class Card extends Block
{
    #[Select(options: Symbol::class)]
    public ?string $icon;

    #[Text(required: true)]
    public string $title;

    #[Textarea]
    public ?string $text;

    #[Relation(to: [Page::class, Doc::class])]
    public Page|Doc|null $page;

    #[Text(max: 500)]
    public ?string $url;

    public function href(): ?string
    {
        if ($this->page !== null) {
            return $this->page->missing ? null : $this->page->url();
        }

        return blank($this->url) ? null : $this->url;
    }
}
