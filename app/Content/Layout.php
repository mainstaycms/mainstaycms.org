<?php

namespace App\Content;

use Mainstay\Content\GlobalSet;
use Mainstay\Fields\Relation;
use Mainstay\Fields\Text;
use Mainstay\Fields\Textarea;

/* What every page has around it: the header's menu and the repository it
   links to, and the footer's columns and lines. */
class Layout extends GlobalSet
{
    #[Relation(to: Page::class, localized: true)]
    public array $menu;

    /* Mainstay's repository, linked from the header, and its plan, its
       decisions and its licence from the footer. */
    #[Text(max: 500)]
    public ?string $repository;

    /* Under the name in the footer. */
    #[Textarea(localized: true)]
    public ?string $tagline;

    /* The footer's column of what Mainstay is. */
    #[Relation(to: Page::class, localized: true)]
    public array $product;

    /* The footer's column of where to read about it. */
    #[Relation(to: [Page::class, Doc::class], localized: true)]
    public array $reading;

    /* The footer's last line, at its start and at its end. */
    #[Text(localized: true)]
    public ?string $copyright;

    #[Text(localized: true)]
    public ?string $requirements;
}
