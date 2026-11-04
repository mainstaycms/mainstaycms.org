<?php

namespace Database\Seeders;

use App\Content\Phase;
use Illuminate\Database\Seeder;

/*
 | The phases of Mainstay's plan, docs/plan.md in its repository, in its
 | order: the ones built, the work between two of them, and the ones to
 | come.
 */
class RoadmapSeeder extends Seeder
{
    use WritesContent;

    private const PHASES = [
        [1, 'Declarations', true, 'A content type is a PHP class and its fields are typed properties with attributes. A registry reads them into one field list, and each field type says what column it wants, how it validates and how it casts.'],
        [2, 'Schema', true, 'The field list becomes tables: one per type, a `_locales` table beside it for what is translated. `mainstay:sync` brings a development database in step, and `mainstay:schema:check` fails CI where a database has drifted.'],
        [3, 'Content, from code', true, 'One query layer for reads and writes, taking filters and sorting as data rather than closures, asking Laravel\'s Gate on every call. Seeders, templates and the API all go through it.'],
        [4, 'Public rendering', true, 'One fallback route turns a path into an entry and a Blade view, after any route the site wrote itself. Declare a type, write an entry, visit its URL.'],
        [5, 'Rich text and blocks', true, 'An entry\'s `content` is one `Content` field, a document of text and blocks in a JSON column. The blocks a type allows are named by a class-level `#[Blocks]` attribute, and a block is a class with fields and a view, rendered through Blade.'],
        [6, 'Media', true, 'An image library shared across an installation. A JPEG, PNG, GIF or WebP is recognised by its bytes, and written at every declared size in AVIF and WebP when it is uploaded.'],
        [7, 'Globals, taxonomies and relations', true, 'Entries point at each other through relations a read follows to a depth. Tags are the entries of a taxonomy, and a global holds settings such as a menu.'],
        [8, 'Drafts, publishing, revisions, trash', true, 'A change waits as a draft of only what differs until it is published in one transaction. The newest 50 replaced versions of an entry are kept as revisions (a setting, or all of them), and a deleted entry goes to the trash first.'],
        [9, 'Identity', true, 'Accounts of the admin\'s own behind a login, and roles whose capabilities each type derives, which Gate asks about on every call.'],
        [10, 'The admin', true, 'Every registered type gets a list and a form in every language, with buttons for drafts, publishing, the trash and each entry\'s history.'],
        [11, 'Accounts in the admin', true, 'Accounts are invited by mail, roles are a grid of capabilities, and nobody hands out a capability they do not hold themselves.'],
        [12, 'The page builder', true, 'Every entry has content, text and the blocks its type names. A type with a page is edited on the page the site draws, text typed in place and a block\'s fields in a dialog.'],
        [13, 'The API as a product', true, 'The query layer over HTTP as REST and as an MCP server, behind tokens with capabilities of their own, every payload described by a JSON Schema.'],
        [null, 'AI connectors', true, 'A Connectors screen for every provider laravel/ai speaks: its key from the environment or saved encrypted, a test of the key, and the provider chosen for each kind of work.'],
        [14, 'The second locale and the second site', false, 'Proving what the schema already has room for by configuring a second of each: publish state and trash per locale, fallback as an opt-in, and a site matched to the request\'s host.'],
        [15, 'Onboarding', false, 'An installation checklist and a list per user, each step a check on what is already true, so nothing is stored and nothing drifts. The checklist is the dashboard\'s first widget.'],
        [16, 'Template parts', false, 'A navigation or a footer built from blocks, stored once per site and drafted and published as a global is, and edited in place the way a page is.'],
    ];

    /* The phase the plan calls the milestone that matters. */
    private const MILESTONE = 4;

    public function run(): void
    {
        foreach (self::PHASES as $position => [$number, $title, $isDone, $summary]) {
            $this->published(Phase::class, [
                'title' => $title,
                'number' => $number,
                'position' => $position + 1,
                'done' => $isDone,
                'milestone' => $number === self::MILESTONE,
                'summary' => $summary,
            ]);
        }
    }
}
