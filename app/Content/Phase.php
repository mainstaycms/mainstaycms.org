<?php

namespace App\Content;

use Illuminate\Support\Collection;
use Mainstay\Content\Entry;
use Mainstay\Facades\Mainstay;
use Mainstay\Fields\Boolean;
use Mainstay\Fields\Number;
use Mainstay\Fields\Textarea;

/*
 | One step of Mainstay's plan, as the roadmap lists it: done, or still to
 | come. No address of its own, so no page: the roadmap and the front page
 | read them, and count the done ones.
 */
class Phase extends Entry
{
    /* Its number in the plan, or none for work between the numbered phases
       or with no place in them yet. */
    #[Number(min: 1)]
    public ?int $number;

    /* Its place in the list. */
    #[Number(required: true)]
    public int $position;

    #[Boolean]
    public bool $done = false;

    /* A phase after which Mainstay could be used in a new way, marked on
       the roadmap. */
    #[Boolean]
    public bool $milestone = false;

    #[Textarea(required: true, localized: true)]
    public string $summary;

    /* Every phase in the plan's order, read once a request: the roadmap and
       the front page each count them more than once. */
    public static function inOrder(): Collection
    {
        return once(fn () => Mainstay::find(self::class, sort: 'position', depth: 0));
    }
}
