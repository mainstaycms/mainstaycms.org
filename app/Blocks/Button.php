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

/*
 | A link drawn as a button, a quieter one, or a link with an arrow: to a page
 | or a doc of the site, found by its id so it follows its slug, or to an
 | address elsewhere. Not drawn while it has nowhere to go, but for its label
 | to be typed in the admin.
 */
#[Icon('button')]
#[Description('A link to a page, a doc or an address, drawn as a button, a quieter button or a link with an arrow.')]
#[Example(['label' => 'Read the docs'])]
class Button extends Block
{
    #[Text(required: true)]
    public string $label;

    #[Relation(to: [Page::class, Doc::class])]
    public Page|Doc|null $page;

    /* Where it goes when it names no page: an address, or `#part` of the page
       it names. */
    #[Text(max: 500)]
    public ?string $url;

    #[Select(options: ['button' => 'Button', 'secondary' => 'Quieter button', 'link' => 'Link with an arrow'])]
    public string $style = 'button';

    public function href(): ?string
    {
        $url = trim((string) $this->url);

        if ($this->page === null) {
            return $url === '' ? null : $url;
        }

        if ($this->page->missing) {
            return null;
        }

        return $this->page->url().(str_starts_with($url, '#') ? $url : '');
    }
}
