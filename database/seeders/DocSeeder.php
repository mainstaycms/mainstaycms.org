<?php

namespace Database\Seeders;

use App\Blocks\CodeSample;
use App\Blocks\Note;
use App\Blocks\Tile;
use App\Blocks\Tiles;
use App\Content\Doc;
use Illuminate\Database\Seeder;

/*
 | Mainstay's documentation, a doc per thing to learn, in the order of
 | Doc::SECTIONS and in reading order within each. Every call, option and
 | path in it is one the package has.
 */
class DocSeeder extends Seeder
{
    use WritesContent;

    public function run(): void
    {
        $sections = [
            'start' => [$this->installation(), $this->firstContentType(), $this->theAdmin()],
            'content' => [$this->fields(), $this->richTextAndBlocks(), $this->images(), $this->languages()],
            'relations' => [$this->relations(), $this->taxonomies(), $this->globals()],
            'rendering' => [$this->routesAndTemplates(), $this->readingContent(), $this->writingABlock()],
            'editing' => [$this->pageBuilder(), $this->draftsAndRevisions(), $this->accountsAndRoles()],
            'extending' => [$this->contentApi(), $this->plugins(), $this->aiConnectors()],
        ];

        foreach ($sections as $section => $docs) {
            foreach ($docs as $index => $doc) {
                $this->published(Doc::class, [...$doc, 'section' => $section, 'position' => $index + 1]);
            }
        }
    }

    private function installation(): array
    {
        return [
            'title' => 'Installation',
            'slug' => 'installation',
            'summary' => 'Add Mainstay to a Laravel application, publish its assets, and make the first account.',
            'content' => $this->blocks(...[
                ...$this->paragraphs(<<<'TEXT'
                    Mainstay is a Composer package that runs inside the Laravel application you already have. It brings its own tables, its own admin at `/admin` and a content API at `/api/mainstay`, and answers every path your own routes do not.

                    ## Requirements
                    TEXT),
                ['block' => Tiles::class, 'data' => ['style' => 'grid'], 'inner' => [
                    ['block' => Tile::class, 'data' => ['label' => 'PHP', 'value' => '8.3 or newer, with GD built for AVIF and WebP']],
                    ['block' => Tile::class, 'data' => ['label' => 'Laravel', 'value' => '13']],
                    ['block' => Tile::class, 'data' => ['label' => 'Database', 'value' => 'SQLite, PostgreSQL, MySQL or SQL Server']],
                ]],
                ...$this->paragraphs(<<<'TEXT'
                    GD is what images are resized with, and every copy is written as AVIF and WebP, so the GD in your PHP must be built with both. An upload on a GD without them is refused, naming the format it lacks; `gd_info()` lists what yours supports.

                    ## Install the package

                        composer require mainstay/cms
                        php artisan vendor:publish --tag=mainstay-assets
                        php artisan migrate

                    The assets are the admin's built scripts, styles and fonts, copied to `public/vendor/mainstay`. Nothing in Mainstay asks you to run a JavaScript build. The migrations are the package's own: sites, accounts, roles, the media library, drafts, revisions, the lookup of paths, API tokens and AI connectors.

                    ## Configure it

                    The defaults work as they are. To change them, publish the config:

                        php artisan vendor:publish --tag=mainstay-config

                    `config/mainstay.php` sets where the admin is (`MAINSTAY_PATH`, and `MAINSTAY_DOMAIN` for a host of its own), the API's prefix, the middleware that runs after the admin's own (`middleware`), whether the public catch-all runs and with which middleware, the API's middleware (`api.middleware`), the content locales, the disks images are kept on, how many revisions an entry keeps (50) and which plugins load.

                    ## Turn on schema sync in development

                    Content types get real tables, and in development `mainstay:sync` keeps them in step with your classes. It is off until you turn it on, and it refuses to run in production whatever the flag says:

                        MAINSTAY_SCHEMA_SYNC=true
                    TEXT),
                ['block' => Note::class, 'data' => ['text' => 'Production gets its schema from migrations you write. Run `php artisan mainstay:schema:check` in CI, against a database built by those migrations: it lists what differs from your classes and fails until a migration covers it. It is not read-only: it creates a scratch table for each type to compare against, and drops it again.']],
                ...$this->paragraphs(<<<'TEXT'
                    ## Make the first account

                    There is no public sign-up, so a new server has no page the first visitor could claim. Make the first one on the server; it asks for a name and a password:

                        php artisan mainstay:user you@example.com --role=administrator

                    Then sign in at `/admin` and invite everyone else from Users.

                    ## Upgrading

                    After upgrading the package, publish the assets again with `--force`. An upgrade can add a column Mainstay keeps for itself, so run `mainstay:schema:check` in CI against a database built by your migrations before you deploy, and write a migration for anything it reports.
                    TEXT),
            ]),
        ];
    }

    private function firstContentType(): array
    {
        return [
            'title' => 'Your first content type',
            'slug' => 'first-content-type',
            'summary' => 'Declare a type as a PHP class, register it, give it a table and a view, and write its first entry.',
            'content' => $this->blocks(...[
                ...$this->paragraphs(<<<'TEXT'
                    A content type is a class extending `Entry`. Its typed properties, each with a field attribute, are its fields: one declaration gives a field its column, its form field in the admin, its validation rules, its JSON Schema and its PHP type. In development `mainstay:sync` builds the table; in production a migration you write does.

                    ## Declare it
                    TEXT),
                ['block' => CodeSample::class, 'data' => ['caption' => 'app/Content/Article.php', 'code' => <<<'PHP'
                    namespace App\Content;

                    use Carbon\CarbonImmutable;
                    use Mainstay\Content\Entry;
                    use Mainstay\Content\Route;
                    use Mainstay\Fields\Date;
                    use Mainstay\Fields\Text;
                    use Mainstay\Fields\Textarea;

                    #[Route('/blog/{slug}')]
                    class Article extends Entry
                    {
                        #[Text(required: true, localized: true)]
                        public string $slug;

                        #[Textarea(localized: true)]
                        public ?string $summary;

                        #[Date(required: true)]
                        public CarbonImmutable $publishedOn;
                    }
                    PHP]],
                ...$this->paragraphs(<<<'TEXT'
                    Every entry already has a `title` and `content` without declaring them. `#[Route]` gives it a path built from its fields, so this one is served at `/blog/` and its slug.

                    ## Register it

                    Types are registered, not discovered. Name them in a service provider's `boot()`:

                        use App\Content\Article;
                        use Mainstay\Facades\Mainstay;

                        Mainstay::types([Article::class]);

                    ## Give it a table

                        php artisan mainstay:sync

                    The type gets a table named after its handle, `article`, with a column per field, and `article_locales` beside it for the fields that are translated. Sync asks before it drops a column or retypes one in a way that changes what is stored; a renamed property reads as a dropped column and a new one.

                    ## Give it a view

                    An entry renders with the Blade view named after its type's handle, and gets itself as `$entry`:
                    TEXT),
                ['block' => CodeSample::class, 'data' => ['caption' => 'resources/views/article.blade.php', 'code' => <<<'BLADE'
                    <article>
                        <h1>{{ $entry->title }}</h1>
                        <p>{{ $entry->publishedOn->isoFormat('D MMMM YYYY') }}</p>
                        <div class="prose">{{ $entry->content }}</div>
                    </article>
                    BLADE]],
                ...$this->paragraphs(<<<'TEXT'
                    ## Write an entry

                    Write one in the admin, which now lists Articles, or from code. A seeder, a command or tinker has nobody signed in, so it says it writes on its own authority:

                        Mainstay::create(Article::class, [
                            'title' => 'Hello',
                            'slug' => 'hello',
                            'publishedOn' => '2026-11-03',
                        ], overrideAccess: true);

                    Visit `/blog/hello`. The same call from the admin, a seeder or the API is validated by the same rules, so none of them can save what another would refuse. Permissions apply to all of them too, except where code passes `overrideAccess: true`, as this one does.
                    TEXT),
            ]),
        ];
    }

    private function theAdmin(): array
    {
        return [
            'title' => 'The admin',
            'slug' => 'the-admin',
            'summary' => 'Signing in, the screens every registered type gets, and inviting the people who write with you.',
            'content' => $this->prose(<<<'TEXT'
                The admin is at `/admin`, or wherever `MAINSTAY_PATH` and `MAINSTAY_DOMAIN` put it. It is drawn in Blade, and it is a transport over the same query layer your code uses: a form validates what a seeder's write validates and is refused what Laravel's Gate refuses.

                ## Signing in

                The admin has accounts of its own, kept apart from your application's users, under a session cookie of its own, `mainstay_session`, so an editor's session is never a visitor's. A wrong password and an unknown email get the same answer, so the form does not tell anyone which accounts exist, and five failures for one email from one address lock it for a minute. "Forgot your password?" mails a link to choose a new one.

                The first account is made on the server, since the admin has no public sign-up and no account yet to invite from:

                    php artisan mainstay:user you@example.com --role=administrator

                `--role` names a role, `administrator`, `editor` or one of your own; `--name` sets the name the admin shows, and is asked for when you leave it out. The password is always asked for, so it never lands in the shell's history.

                ## Inviting people

                Under Users, someone holding `manage_users` makes an account with a name, an email and a role. It has no password yet: Mainstay mails it a link to choose one, which works for an hour unless `auth.passwords.mainstay.expire` says otherwise. An expired link is answered as a reset is, by asking for another. Nobody can give a role holding a capability they do not hold themselves.

                ## What each type gets

                Every entry type gets:

                - A list of its entries in each language, with each one's status, author and last change, a status filter, a search and its trash.
                - An edit screen with a Details tab holding its fields, and a Content tab for a type with a page.
                - Save draft and Publish, the entry's history, and the author it is filed under.

                A taxonomy gets the list, the search, the trash and the edit screen, but its terms are written live: they have Save instead of Save draft and Publish, and no status filter, history or author to choose.

                A global has none of the list, the status filter, the trash or the Content tab. It sits in the sidebar under Globals and opens as one form, with Save draft and Publish and a history.

                The sidebar also holds the Dashboard, and Media, Users, Roles and Connectors, the last for AI providers; those four show only to someone allowed them. In the account menu are your own settings and API tokens. A plugin's screens appear in the sidebar too, behind a capability of their own. An entry of a routed type is made the front page from the menu on its form, with Use as front page.
                TEXT),
        ];
    }

    private function fields(): array
    {
        return [
            'title' => 'Fields',
            'slug' => 'fields',
            'summary' => 'The field attributes a type is declared with, what each stores, and the arguments they share.',
            'content' => $this->blocks(...[
                ...$this->paragraphs(<<<'TEXT'
                    A field is an attribute on a typed property. The attribute is the field type itself: it knows its column, its validation rules, its JSON Schema, how it casts both ways and the component that draws it in the admin. The property's own type says the rest, so `#[Text] public ?string $subtitle` is an optional string called subtitle. One declaration therefore gives the field its column, its form field, its validation rules, its JSON Schema and its PHP type; the column still needs a migration in production.

                    ## The arguments every field takes

                    - `required:` makes a write need a value. Left out, it follows the property: a nullable property is optional, any other required. Two exceptions: a `#[Boolean]` is required only with `required: true`, and a `#[Relation]` or `#[Terms]` that holds a list is required only with `required: true`, since an empty list is a list holding none.
                    - `localized:` gives the field a value per language, kept in the type's `_locales` table.
                    - `label:` is what the admin calls it, when the property's name does not say it well.

                    ## The field types
                    TEXT),
                ['block' => Tiles::class, 'data' => ['style' => 'grid'], 'inner' => [
                    ['block' => Tile::class, 'data' => ['label' => '#[Text]', 'value' => 'A line of text, `max:` 255 by default: the column is as wide as the rule.']],
                    ['block' => Tile::class, 'data' => ['label' => '#[Textarea]', 'value' => 'Long plain text, kept as written.']],
                    ['block' => Tile::class, 'data' => ['label' => '#[Number]', 'value' => 'An int or a float, as the property says, with `min:` and `max:`.']],
                    ['block' => Tile::class, 'data' => ['label' => '#[Boolean]', 'value' => 'True or false. `required: true` means it must be true.']],
                    ['block' => Tile::class, 'data' => ['label' => '#[Date]', 'value' => 'A day, or a moment in UTC with `time: true`.']],
                    ['block' => Tile::class, 'data' => ['label' => '#[Select]', 'value' => 'One of `options:`, a list, value to label, or a backed enum.']],
                    ['block' => Tile::class, 'data' => ['label' => '#[RichText]', 'value' => 'A document of headings, paragraphs, lists, quotes and code.']],
                    ['block' => Tile::class, 'data' => ['label' => '#[Image]', 'value' => 'An image from the library, at the `sizes:` it declares.']],
                    ['block' => Tile::class, 'data' => ['label' => '#[Relation]', 'value' => 'One entry or a list of them, of the types in `to:`.']],
                    ['block' => Tile::class, 'data' => ['label' => '#[Terms]', 'value' => 'An entry\'s terms from the taxonomy in `of:`.']],
                ]],
                ...$this->paragraphs(<<<'TEXT'
                    ## An example
                    TEXT),
                ['block' => CodeSample::class, 'data' => ['caption' => 'app/Content/Event.php', 'code' => <<<'PHP'
                    #[Route('/events/{slug}')]
                    class Event extends Entry
                    {
                        #[Text(required: true, localized: true)]
                        public string $slug;

                        #[Date(time: true)]
                        public ?CarbonImmutable $startsAt;

                        #[Number(min: 0)]
                        public ?float $price;

                        #[Select(options: ['talk' => 'Talk', 'workshop' => 'Workshop'])]
                        public string $kind = 'talk';

                        #[Boolean]
                        public bool $soldOut = false;

                        #[Internal]
                        #[Textarea]
                        public ?string $notes;
                    }
                    PHP]],
                ...$this->paragraphs(<<<'TEXT'
                    A default on the property is what a new row holds when nothing is given. Each field type checks the property it sits on when the type is first read, so `#[Date] public string $when` is refused with a message naming it, rather than a `TypeError` two frames away.

                    ## Internal fields

                    `#[Internal]` beside a field keeps it from anyone who may not edit the type: it is left out of what they read, and refused as a filter or a sort, so its value cannot be read back from which entries match. It suits, for example, an editor's note or a cost price.

                    ## Fields of your own

                    A field type is a subclass of `Mainstay\Fields\Field`, with no registration call: the attribute on the property is the instance. It names its column, its rules and its casts, and the Blade component that draws it.
                    TEXT),
            ]),
        ];
    }

    private function richTextAndBlocks(): array
    {
        return [
            'title' => 'Rich text and blocks',
            'slug' => 'rich-text-and-blocks',
            'summary' => 'How an entry\'s content is stored and printed, which blocks it may hold, and where a rich text field fits.',
            'content' => $this->prose(<<<'TEXT'
                Every entry has content: one document of text and blocks. It is stored as a ProseMirror document, in a JSON column of its own, never as markup, so a block is typed data rather than HTML to parse. Like any JSON field, it is not filtered or sorted on.

                ## What the text holds

                Paragraphs, headings of six levels, bullet and numbered lists, quotes, code blocks, horizontal rules and line breaks, with links, emphasis, strong and inline code as marks. Printing the content draws all of it, and every block in it through the block's own view:

                    <div class="prose">{{ $entry->content }}</div>

                ## Which blocks it may hold

                A type names its blocks on its class. Without `#[Blocks]`, its content is text alone:

                    #[Route('/{slug}')]
                    #[Blocks([Hero::class, Section::class, Cta::class])]
                    class Page extends Entry
                    {
                        #[Text(required: true, localized: true)]
                        public string $slug;
                    }

                A type with no `#[Route]` has no page to build blocks on, so naming blocks on one is refused. A block can name blocks of its own, held inside it: see Writing a block.

                ## Stored as data

                A block in a document is a node with its id, its type and its fields, `{type: 'block', attrs: {id, type, data}}`, holding the blocks inside it as its content. Its fields are checked, cast and described by the same contract an entry's are, wherever in the document they sit. The id is given when the block is first written and kept, which is how a block that moved is told from one that was replaced.

                ## Reading the blocks

                `$entry->content` is a `?Document`, null when the entry has no content, so `$entry->content?->blocks()` is the safe call. A `Document`'s `blocks()` lists the blocks at its top level, in order, and `block($id)` finds one by its id. A view can look at what a page holds before it draws it, as a template asking whether the page opens on a hero.

                ## Content in each language

                Content is localized: each language has a document of its own, translated whole. A field inside a block is therefore neither localized nor internal on its own. A type that wants one content for every language redeclares `$content` with `#[Content]` and no `localized`.

                ## Rich text fields

                For a second body on an entry, an abstract beside the article, declare a field with `#[RichText]` on a `?Document` property. It holds text alone, no blocks, and prints the same way. There is no empty document: a body left blank is null, so declare it nullable.
                TEXT),
        ];
    }

    private function images(): array
    {
        return [
            'title' => 'Images',
            'slug' => 'images',
            'summary' => 'The media library, the sizes an image field declares, and printing an image as a picture.',
            'content' => $this->blocks(...[
                ...$this->paragraphs(<<<'TEXT'
                    Images live in one media library for the whole installation. A field holds an image's id, and declares the sizes it is printed at. Every one of those sizes is written as AVIF and WebP when a file is uploaded. A size added later is written by `mainstay:media:reprocess`, which queues the missing copies of every image.

                    ## Declare the sizes
                    TEXT),
                ['block' => CodeSample::class, 'data' => ['caption' => 'app/Content/Article.php', 'code' => <<<'PHP'
                    #[Image(sizes: ['cover' => [1600, 900], 'card' => [640]])]
                    public ?Media $cover;
                    PHP]],
                ...$this->paragraphs(<<<'TEXT'
                    A width and a height crop to that shape around the image's focal point; a width alone scales. Neither enlarges an image smaller than the size. The property is always nullable, since null is no image chosen; `required: true` is what makes a write need one.

                    ## Print one

                        {{ $entry->cover?->picture('cover') }}

                    `picture()` prints a `<picture>` with the AVIF as a source and the WebP as the `<img>`, with the alt text and the copy's real width and height, lazy unless you pass other attributes. For your own markup there are `url('card')`, `url('card', 'avif')`, `width('card')` and `height('card')`.

                    ## Upload from code

                        $cover = Mainstay::media()->upload(
                            database_path('seeders/images/harbour.png'),
                            alt: ['en' => 'A harbour at dusk, two boats moored'],
                            overrideAccess: true,
                        );

                        Mainstay::update(Article::class, $id, ['cover' => $cover->id], overrideAccess: true);

                    Alt text names every content locale; an empty string marks an image as decorative, and a language left out is refused. The focal point is two percentages, across and down, `[50, 50]` by default, and is set in the library's screen too.
                    TEXT),
                ['block' => Note::class, 'data' => ['text' => 'An image is recognised by its bytes, not its name: JPEG, PNG, GIF and WebP. SVG is refused, since an SVG can carry script.']],
                ...$this->paragraphs(<<<'TEXT'
                    ## What is kept where

                    Each file is held once, by the hash of its bytes, so uploading the same file twice is refused, naming the image that holds it. The copies go to the public disk, `MAINSTAY_MEDIA_DISK`, and are linked to as plain files named from everything they are made of, so nothing ever needs invalidating. The original goes to a private disk, `MAINSTAY_MEDIA_ORIGINALS`, and is never served: it still carries the camera's EXIF, GPS included, which the copies are written without.

                    ## Adding a size later

                    A size added to a field reaches images uploaded before it with one command, which queues a job per image writing the copies that are missing:

                        php artisan mainstay:media:reprocess

                    ## Missing images

                    An image in the trash, or gone, still reads as a `Media`, marked `missing` and holding its id. It prints nothing and links nowhere, and once it is back every place it was used shows it again.
                    TEXT),
            ]),
        ];
    }

    private function languages(): array
    {
        return [
            'title' => 'Languages',
            'slug' => 'languages',
            'summary' => 'Content locales, where each is served, and which fields have a value per language.',
            'content' => $this->doc(...[
                ...$this->paragraphs(<<<'TEXT'
                Content is translated per field. A title and a slug get a value in each language; a date or a price stays one value for all of them. Each field says which it is with `localized: true`.

                ## The locales

                `locales` in `config/mainstay.php` lists the languages content is written in, the default first, each with where it is served: a path prefix, a host, or both.

                    'locales' => ['en' => '/', 'nl' => '/nl'],

                    'locales' => ['en' => 'https://example.com', 'nl' => 'https://example.nl'],

                Either every locale names a host or none does. A public page sets the application's locale to the one it is served in, so `__()` and the reads a template makes follow it. Out of the box there is one locale, your `APP_LOCALE`, served at `/`.

                ## Where translations are kept

                A type's localized fields live in its `_locales` table, a row per language, beside the main table holding the fields shared by all of them. Reads take a `locale:`; without one they read the request's.

                ## Writing a translation

                `update()` without a locale updates the entry as the request's locale reads it. Pass one the entry has no row in yet and it adds that translation, asking for its required localized fields since nothing is stored for them:

                    Mainstay::update(Article::class, $id, [
                        'title' => 'Hallo',
                        'slug' => 'hallo',
                    ], locale: 'nl', overrideAccess: true);

                ## Paths per language

                A route takes one pattern for every locale, or one per locale where a segment is translated:

                    #[Route(['en' => '/blog/{slug}', 'nl' => '/nieuws/{slug}'])]

                The pattern holds no locale prefix: the prefix or host is the catch-all's, from the config.

                ## What follows the language, and what does not

                - An entry's content is translated whole, a document per language.
                - An image's alt text has a value per locale.
                - Terms are the same in every language; each term is translated itself.
                - An entry not written in a language is absent there: its path answers 404, and a relation to it reads as missing.

                ## Not built yet

                There is no fallback from one language to another, and publishing and the trash act on every language at once.
                TEXT),
                $this->p('Both are planned; the ', $this->link('roadmap', '/roadmap'), ' says when.'),
            ]),
        ];
    }

    private function relations(): array
    {
        return [
            'title' => 'Relations and depth',
            'slug' => 'relations',
            'summary' => 'Entries pointing at each other, how far a read follows them, and what a missing target reads as.',
            'content' => $this->blocks(...[
                ...$this->paragraphs(<<<'TEXT'
                    A relation is an entry pointing at others, stored as their plain ids with no foreign key. The property says whether it holds one or a list.

                    ## Declare one
                    TEXT),
                ['block' => CodeSample::class, 'data' => ['caption' => 'app/Content/Article.php', 'code' => <<<'PHP'
                    #[Relation(to: Person::class)]
                    public ?Person $author;

                    #[Relation(to: Article::class)]
                    public array $related;

                    #[Relation(to: [Page::class, Doc::class])]
                    public array $reading;
                    PHP]],
                ...$this->paragraphs(<<<'TEXT'
                    One type in `to:` stores the id: one in a column a `where` can filter on, a list as a JSON list of ids. Several store each as its type and id, since an id alone does not say which table it is in. For one of several types, type the property as a union of them. A list holds none rather than null.

                    ## Depth

                    A read follows relations to a depth, 1 by default: the entries a relation points at are loaded, and their own relations hold references. Ask for more where a page needs it:

                        $article = Mainstay::findById(Article::class, $id, depth: 2);

                    Depth 0 loads no related entries past the entry itself, which is right for a list that only prints titles. Images load at every depth. Every target is read through the same query every read starts from, so the site, the locale, the trash and Gate apply to it as to anything else read. A read loads each type's targets in one query, whatever the number of entries pointing at them.

                    ## Missing targets

                    A target that was not loaded is still an instance of its type, marked `missing` and holding its id. That happens when depth ran out, or the target is in the trash, gone, not written in the locale read, or of a type the reader may not read. A view asks before it links:
                    TEXT),
                ['block' => CodeSample::class, 'data' => ['caption' => 'resources/views/article.blade.php', 'code' => <<<'BLADE'
                    @foreach (collect($entry->related)->reject->missing as $article)
                        <a href="{{ $article->url() }}">{{ $article->title }}</a>
                    @endforeach
                    BLADE]],
                ...$this->paragraphs(<<<'TEXT'
                    Written back as it was read, a missing target keeps its place, so an entry saved while its target is in the trash still points at it when it comes back. A reference to a type the field no longer names is dropped from the next read and save.

                    ## Filtering on a relation

                    A relation of one type to one entry is a column, and a `where` takes the id:

                        Mainstay::find(Article::class, where: ['author' => $person->id]);

                    For a list of terms and the entries holding one, use a taxonomy: see Tags and taxonomies.
                    TEXT),
            ]),
        ];
    }

    private function taxonomies(): array
    {
        return [
            'title' => 'Tags and taxonomies',
            'slug' => 'taxonomies',
            'summary' => 'Terms that group entries, the field that holds them, and finding the entries filed under one.',
            'content' => $this->prose(<<<'TEXT'
                A taxonomy is a type whose entries are terms. A term is an entry in every way, with its tables, its route, its view and `url()`; what makes it a term is that a terms field points at it and a read filters by it. Taxonomies are flat.

                ## Declare one

                    #[Route('/blog/tag/{slug}')]
                    class Tag extends Taxonomy
                    {
                        #[Text(required: true, localized: true)]
                        public string $slug;
                    }

                Register it with your other types. Then give entries a field holding its terms, in the order given:

                    #[Terms(of: Tag::class)]
                    public array $tags;

                ## Where terms are kept

                Not in a column: in rows of the taxonomy's pivot table, `tag_entries`, which is what lets a read ask the reverse question. The pivot has no locale, so an entry has the same terms in every language, and each term is translated itself. A terms field is therefore never `localized`, and only sits on an entry, not inside a block or on a global.

                ## The entries holding a term

                A term's page lists what is filed under it:

                    $articles = Mainstay::paginate(Article::class,
                        where: ['tags' => $entry->id],
                        sort: '-publishedOn',
                        perPage: 10,
                    );

                `=` finds the entries holding one term and `in` those holding any of several, `['tags' => ['in' => [3, 5]]]`. A term in the trash gathers nothing while it is there. Terms are not sorted on.

                ## In the admin

                A terms field is a tag input: pick a term, or type a new one and it is made when the entry is saved. Terms have one capability per taxonomy, `manage_tags` here, and `manage_terms` covers every taxonomy.
                TEXT),
        ];
    }

    private function globals(): array
    {
        return [
            'title' => 'Globals',
            'slug' => 'globals',
            'summary' => 'Settings with one value per site, such as a menu and a footer, read from any view.',
            'content' => $this->blocks(...[
                ...$this->paragraphs(<<<'TEXT'
                    A global is a type with one row per site and no route: the menu, the footer line, a contact address. It is a class extending `GlobalSet`, so named because `Global` is a reserved word in PHP.

                    ## Declare one
                    TEXT),
                ['block' => CodeSample::class, 'data' => ['caption' => 'app/Content/Layout.php', 'code' => <<<'PHP'
                    class Layout extends GlobalSet
                    {
                        #[Relation(to: Page::class, localized: true)]
                        public array $menu;

                        #[Textarea(localized: true)]
                        public ?string $tagline;

                        #[Text(localized: true)]
                        public ?string $copyright;
                    }
                    PHP]],
                ...$this->paragraphs(<<<'TEXT'
                    Its fields are a type's fields: a relation, an image or a select works here as it does on an entry. Register it with your other types and sync.

                    ## Read it

                        $layout = Mainstay::global(Layout::class);

                    It is this site's one, in the locale read, or null where it has not been written in that locale: absent, as an untranslated entry is, rather than defaults nobody stored. A layout component reads it once and draws the header and footer from it.

                    ## Write it

                        Mainstay::saveGlobal(Layout::class, [
                            'tagline' => 'A CMS that lives in your Laravel application.',
                        ], overrideAccess: true);

                    In the admin, a global is a form of its own with Save draft and Publish and a history, as an entry has. Its capabilities are named after it, `edit_layout` and `publish_layout`, and `edit_globals` and `publish_globals` cover every global.
                    TEXT),
                ['block' => Note::class, 'data' => ['text' => 'A global holds fields, not blocks. A navigation or a footer built from blocks is a template part, which Mainstay does not have yet.']],
                $this->p('Template parts are planned; the ', $this->link('roadmap', '/roadmap'), ' says when.'),
            ]),
        ];
    }

    private function routesAndTemplates(): array
    {
        return [
            'title' => 'Routes and templates',
            'slug' => 'routes-and-templates',
            'summary' => 'How a path finds its entry, the view the entry renders with, and the front page.',
            'content' => $this->blocks(...[
                ...$this->paragraphs(<<<'TEXT'
                    Public pages are served by one catch-all route. It takes the locale from the request's host and path prefix, finds the entry the rest of the path leads to, and renders that entry's view. A path that leads nowhere is an ordinary 404.

                    ## Declaring a path

                    `#[Route]` on a type builds each entry's path from its fields:

                        #[Route('/docs/{slug}')]

                    A `{field}` takes the field's value. Every segment is lowercase letters and numbers between hyphens, what `Str::slug()` writes, and a routed field's values and a routed select's options are held to it. Each save writes the entry's path into a lookup table, `uris`, so finding a page is one indexed read whatever the type, and two entries cannot claim one path. `$entry->url()` is the locale's base and that path.

                    ## Your routes come first

                    The catch-all is a fallback route, so any route your application declares wins whatever order it was registered in. A path with a trailing slash is sent to the one without, so every page has one address. Its middleware is `site.middleware` in the config, `web` by default; set `site.enabled` to false where the front end is built elsewhere.

                    ## The view

                    An entry renders with its own view if it names one, else its type's `#[Template]`, else the view named after its type's handle: `article.blade.php` for `Article`. It gets the entry as `$entry`.
                    TEXT),
                ['block' => CodeSample::class, 'data' => ['caption' => 'app/Content/Doc.php', 'code' => <<<'PHP'
                    #[Route('/docs/{slug}')]
                    #[Template('docs.page', 'docs.wide')]
                    class Doc extends Entry
                    {
                        // ...
                    }
                    PHP]],
                ...$this->paragraphs(<<<'TEXT'
                    The first view is the one entries render with; any after it is one an entry may pick as its own instead, and no other, since a view is written against the type's fields. A view named and missing is an error, never a quiet step down to another.

                    ## The front page

                    Any entry of a routed type can be the front page, served at `/`. Choose it with Use as front page in the menu on the entry's form, or from code:

                        Mainstay::setFrontPage(Page::class, $home->id);

                    Its path becomes `/` in every language it has, and the page it replaces takes its own path again. The front page cannot be moved to the trash until another is chosen. `Mainstay::frontPage()` gives its type and id.

                    ## Changing a slug

                    A save rebuilds the path from the pattern, and the old path stops answering. Redirects from old paths are not built.
                    TEXT),
            ]),
        ];
    }

    private function readingContent(): array
    {
        return [
            'title' => 'Reading content',
            'slug' => 'reading-content',
            'summary' => 'The query layer every template, seeder, admin screen and API call reads and writes through.',
            'content' => $this->prose(<<<'TEXT'
                There is one query layer, and HTTP is a transport over it. A template, a seeder, the admin and the API all call the same methods with the same arguments, so none of them is handed another shape or a weaker validation. Permissions are the same too, unless code passes `overrideAccess: true`.

                ## Reads

                    Mainstay::find(Article::class, where: [...], sort: [...], limit: 10);
                    Mainstay::paginate(Article::class, perPage: 10);
                    Mainstay::findById(Article::class, $id);
                    Mainstay::findByUri('/blog/hello');

                `find()` gives a collection and `paginate()` Laravel's paginator, with `page:` read from the request unless you pass it. Every read takes `locale:`, `depth:` and `overrideAccess:`; `find()` and `paginate()` take `trashed: true` for what is in the trash. Each hands back instances of your class, its fields cast: a date is a `CarbonImmutable`, an image a `Media`, a body a `Document`.

                ## Filtering

                `where` is data rather than a closure, so a query string can carry the same condition a template writes. A value alone means equal; an operator and a value say more:

                    Mainstay::find(Event::class, where: [
                        'kind' => 'workshop',
                        'price' => ['<=' => 20],
                        'startsAt' => ['>' => now()],
                        'id' => ['not_in' => [4, 9]],
                    ]);

                The operators are `=`, `!=`, `<`, `<=`, `>`, `>=`, `in` and `not_in`. A plain list is refused, since it could mean any or all; `in` says which. Only `=` and `!=` take null. Besides fields, `id`, `createdAt` and `updatedAt` can be filtered and sorted on. A JSON field, a body or a list of blocks, is not filtered on.

                ## Sorting

                `sort` is a field, or a list of them, each with `-` in front to descend: `sort: ['-publishedOn', 'title']`. Entries without a value come last either way, on every database.

                ## Writes

                    $article = Mainstay::create(Article::class, $data);
                    Mainstay::update(Article::class, $id, ['title' => 'Hello again']);
                    Mainstay::delete(Article::class, $id);

                `update()` changes only the keys given. A write that does not validate throws Laravel's `ValidationException` naming each field; a query that cannot mean anything throws `BadQuery` saying why. `create()` and `update()` put content live at once; a change meant to wait is a draft.

                ## Access

                Every call asks Laravel's Gate about the account signed in to the admin. On a public page nobody is, and reading is open to everyone while writing is refused. Code that writes on its own authority, a seeder or an import, says so with `overrideAccess: true`, so a refusal is never silently skipped.
                TEXT),
        ];
    }

    private function writingABlock(): array
    {
        return [
            'title' => 'Writing a block',
            'slug' => 'writing-a-block',
            'summary' => 'A block is a class with fields and a Blade view; this is how to write one that is edited in place.',
            'content' => $this->blocks(...[
                ...$this->paragraphs(<<<'TEXT'
                    A block is a class extending `Mainstay\Content\Block`, with fields as a content type has, drawn by the Blade view named after its handle: `Note` by `blocks.note`, `CodeSample` by `blocks.code_sample`. A type lists it in its `#[Blocks]`, and from then on it can be added to a page.

                    ## The class
                    TEXT),
                ['block' => CodeSample::class, 'data' => ['caption' => 'app/Blocks/Note.php', 'code' => <<<'PHP'
                    namespace App\Blocks;

                    use Mainstay\Content\Block;
                    use Mainstay\Content\Description;
                    use Mainstay\Content\Example;
                    use Mainstay\Content\Icon;
                    use Mainstay\Fields\Textarea;

                    #[Icon('quote')]
                    #[Description('A line the reader should not miss, set apart in the accent colour.')]
                    #[Example(['text' => 'Mainstay is not released yet.'])]
                    class Note extends Block
                    {
                        #[Textarea(required: true)]
                        public string $text;
                    }
                    PHP]],
                ...$this->paragraphs(<<<'TEXT'
                    `#[Icon]` names one of the admin's icons, `block`, `paragraph`, `heading`, `quote`, `code`, `list`, `grid`, `columns`, `banner`, `image`, `gallery`, `video`, `star`, `book`, `table`, `link`, `button`, `calendar`, `pin`, `mail`, `person`, `chat` and a few more, or an SVG of your own with `#[Icon(file: 'icons/hero.svg')]`. `#[Description]` is the sentence under its name in the Add block dialog, and `#[Example]` the values it is previewed with there. A block with no example is shown by its icon, its name and its description, if it has one.

                    ## The view
                    TEXT),
                ['block' => CodeSample::class, 'data' => ['caption' => 'resources/views/blocks/note.blade.php', 'code' => <<<'BLADE'
                    <div class="note">
                        <p>@field($block, 'text')</p>
                    </div>
                    BLADE]],
                ...$this->paragraphs(<<<'TEXT'
                    `@field($block, 'text')` prints the field escaped, as `{{ $block->text }}` would. In the admin's Content tab it also marks the text, so an editor types it in place on the page. It takes Text and Textarea fields; any other field is edited in the block's settings and printed as the view likes.

                    ## Blocks inside a block

                    A block names the blocks it holds with `#[Blocks]`, as a type does, and `text: true` lets it hold headings and paragraphs among them. The view prints them with `@inner($block)`, in their order:

                        #[Blocks([Feature::class], text: true)]
                        class Features extends Block {}

                        <section class="features">@inner($block)</section>

                    `@inner` must be the only thing in the element it sits in: in the editor that element becomes where the inner blocks are added and moved. `$block->inner()` lists the blocks inside it, for a view that needs to count them or draw a list of their titles.

                    ## Things to know

                    - `Mainstay::marking()` says whether the view is being drawn for the editor, for a view that has to show an empty field to be typed into.
                    - An image field on a block prints with `picture()` and marks itself, so it is picked and replaced on the page.
                    - A field added to a block already in use needs a default, or a nullable property: the blocks stored before it do not have it.
                    - Every block has an `id`, given when it is first written and kept, for a view that needs one in its markup.
                    TEXT),
            ]),
        ];
    }

    private function pageBuilder(): array
    {
        return [
            'title' => 'The Content tab',
            'slug' => 'page-builder',
            'summary' => 'Editing a page as the site draws it: text typed in place, blocks added, moved and nested.',
            'content' => $this->blocks(...[
                ...$this->paragraphs(<<<'TEXT'
                    An entry of a type with a page has two tabs. Details is its form, the title first. Content is the page itself: the `<main>` of your own view, drawn from the form as it stands, unsaved changes included, with your site's stylesheets and scripts. The header, menu and footer around it are left out, since they are the layout and not this entry.

                    ## What an editor does there

                    - Types a heading, a paragraph or a block's text field where it shows on the page.
                    - Adds a block from the Add block dialog, which previews each one with its example, or from the `/` menu.
                    - Drags a block to another place, or into a block that holds others.
                    - Opens a block's fields and images in a dialog beside it, from a toolbar floating by the selected block.

                    Save draft and Publish save what the tab holds, as they do for the Details tab.

                    ## What your view has to do

                    Print the content in an element of its own. The editor takes over the element the content is printed in, so it must hold the content and nothing else:
                    TEXT),
                ['block' => CodeSample::class, 'data' => ['caption' => 'resources/views/page.blade.php', 'code' => <<<'BLADE'
                    <x-layout :title="$entry->title">
                        <h1>{{ $entry->title }}</h1>
                        <div class="prose">{{ $entry->content }}</div>
                    </x-layout>
                    BLADE]],
                ...$this->paragraphs(<<<'TEXT'
                    A view that prints its content beside other things in one element is shown read-only, and one that does not print it at all says there is nothing to edit; the content's raw form stays in Details either way.

                    ## The editor's styles

                    Rich text fields elsewhere in the admin are drawn in your site's typography too, once you name the stylesheets and the class your content sits in, in a service provider's `boot()`:

                        Mainstay::editorStyles(['/css/site.css'], class: 'prose');

                    Without it they use the admin's own typography.

                    ## Blocks that behave in the editor

                    Your site's script runs on the Content tab as it does on the page. A script that hides things until it runs, tabs or a carousel, should leave every part showing when it runs inside the editor, which it can tell by the content element's JSON script, `script[data-mainstay-content]`, being there when the page loads. In a block's view, `Mainstay::marking()` says the same.
                    TEXT),
                ['block' => Note::class, 'data' => ['text' => 'Only a type with a `#[Route]` has a Content tab: without a page there is nothing to draw the content on, so its content is edited as text in Details.']],
            ]),
        ];
    }

    private function draftsAndRevisions(): array
    {
        return [
            'title' => 'Drafts, revisions and the trash',
            'slug' => 'drafts-and-revisions',
            'summary' => 'Changes that wait until they are published, the versions a publish replaced, and the way back from a delete.',
            'content' => $this->prose(<<<'TEXT'
                The entry's own row is always what is live, so a visitor's read is the same single-row select whether or not someone is working on the page. What an editor is working on waits beside it as a draft.

                ## Drafts

                    $draft = Mainstay::drafts()->save(Article::class, ['title' => 'A better title'], entry: 5);

                    Mainstay::drafts()->publish($draft->id);

                A draft holds only what differs from what is live. Each key saved is compared with the live value and kept only where it differs, so a form posting every field leaves a draft of what was changed, and a draft left changing nothing is deleted. An entry has one draft at a time; saving again merges into it. A draft may be incomplete: what it holds is checked as a write is, but a required field may wait until publish.

                Without `entry:`, the draft is of a new entry, which has no row, id or path until it is published. `find()`, `of()` and `all()` read drafts back, and `discard()` drops one.

                ## Publishing

                Publish puts a draft live in one transaction: each language it holds is validated as an update is, written, and the outgoing version is filed as a revision, then the draft is deleted. Publishing is its own capability, so a role can write drafts for someone else to publish. `Mainstay::create()` and `update()` write live directly and ask for `publish` too.

                ## Revisions

                Every version a publish, or a live write, replaced is kept as a revision, newest first, up to `revisions` in the config, 50 by default; `null` keeps every one. Restoring one makes it the entry's draft, which goes live through publish like any other:

                    $revisions = Mainstay::revisions()->of(Article::class, 5);

                    Mainstay::revisions()->restore($revisions->first()->id);

                The admin's history lists them with who published each.

                ## The trash

                `delete()` moves an entry to the trash in every language at once, and its path stops answering in the same request. Relations to it read as missing while it is there.

                    Mainstay::restore(Article::class, 5);
                    Mainstay::destroy(Article::class, 5);

                `restore()` builds its paths again; where one was taken while it was away, its slug takes the first of `-2`, `-3` that frees it. `destroy()` erases it for good, from the trash only, with its draft, its revisions and its terms.

                ## Hearing about changes

                `Saved`, `Published`, `Deleted` and `Restored`, in `Mainstay\Events`, carry the type and id of live content written. They are dispatched once the write commits, never when it rolls back. Listen with `Event::listen` to clear a cache or ping a build.
                TEXT),
        ];
    }

    private function accountsAndRoles(): array
    {
        return [
            'title' => 'Accounts, roles and capabilities',
            'slug' => 'accounts-and-roles',
            'summary' => 'Who may do what, as capabilities derived from your types and gathered into roles.',
            'content' => $this->blocks(...[
                ...$this->paragraphs(<<<'TEXT'
                    Access is built from capabilities, which are strings, never stored, computed from the types you register, and from roles, each a name holding some of them. The query layer asks Laravel's Gate on every call, and the admin, the API and your code are answered alike, except where code passes `overrideAccess: true` and skips the Gate.

                    ## The capabilities a type derives

                    Each type gets a set named after it, and each capability has a form covering every type of its kind, those you add later included.
                    TEXT),
                ['block' => Tiles::class, 'data' => ['heading' => 'For an Article type', 'style' => 'rows'], 'inner' => [
                    ['block' => Tile::class, 'data' => ['label' => 'Write and change its drafts', 'value' => 'edit_articles']],
                    ['block' => Tile::class, 'data' => ['label' => 'Change what is live', 'value' => 'edit_published_articles']],
                    ['block' => Tile::class, 'data' => ['label' => 'Change someone else\'s', 'value' => 'edit_others_articles']],
                    ['block' => Tile::class, 'data' => ['label' => 'Put it live', 'value' => 'publish_articles']],
                    ['block' => Tile::class, 'data' => ['label' => 'Move it to the trash', 'value' => 'delete_articles']],
                    ['block' => Tile::class, 'data' => ['label' => 'Trash someone else\'s', 'value' => 'delete_others_articles']],
                ]],
                ...$this->paragraphs(<<<'TEXT'
                    `edit_entries` and the rest cover every entry type. A global has `edit_` and `publish_` its handle, covered by `edit_globals` and `publish_globals`; a taxonomy has `manage_tags`, covered by `manage_terms`. Beside them stand `upload_media`, `edit_others_media`, `manage_users`, `manage_connectors`, and `use_` and a plugin's handle for each plugin with screens.

                    ## Roles

                    Two roles come with the package. `administrator` holds `*`, every capability. `editor` holds the forms covering every type, the media library's two, and nothing over accounts or connectors. The rest are yours to make in the admin's Roles, a grid of every capability the types derive. A role holding a capability no type derives any longer holds nothing by it.

                    ## Nobody hands out more than they hold

                    An account can give a role, or make one, only of capabilities it holds itself, and the installation is never left without an account that can manage accounts. The same holds for API tokens: a token holds no more than its account, and loses what its account loses.

                    ## From code

                        use Mainstay\Auth\Role;

                        $editor = Role::query()->where('name', 'editor')->value('id');

                        Mainstay::accounts()->create('Ada', 'ada@example.com', role: $editor, overrideAccess: true);

                    The account has no password until its owner chooses one from the link `invite()` mails it; the admin's Users screen mails it as it makes the account. `saveRole()` makes or changes a role, and `assign()` gives an entry to another author:

                        Mainstay::assign(Article::class, 5, owner: 3);
                    TEXT),
                ['block' => Note::class, 'data' => ['text' => 'A policy of your own binds API tokens only if it asks `$user->holds(...)`, as Mainstay\'s do, rather than reading the role.']],
            ]),
        ];
    }

    private function contentApi(): array
    {
        return [
            'title' => 'The content API',
            'slug' => 'content-api',
            'summary' => 'The query layer over JSON and as an MCP server, behind tokens with capabilities of their own.',
            'content' => $this->blocks(...[
                ...$this->paragraphs(<<<'TEXT'
                    The API is at `/api/mainstay`, under `MAINSTAY_API_PREFIX`, and answers JSON. It calls the same operations your templates do, so a filter, a refusal and a payload are the same whichever way you reach them.

                    ## Reading without a token

                    Without a token, a read sees only the types marked `#[PublicRead]`, never an internal field, follows relations one level deep and writes nothing:

                        #[PublicRead]
                        #[Route('/blog/{slug}')]
                        class Article extends Entry { /* ... */ }

                    ## Tokens

                    An account makes its own under API tokens in the account menu, ticking the capabilities the token holds; the server makes one with a command:

                        php artisan mainstay:token ada@example.com "CI build" --capability=edit_articles

                    A token holds the capabilities ticked for it, never one its account does not hold, and at use no more than the account holds then: one with none ticked reads, and `*` holds everything the account does. A program sends it as `Authorization: Bearer mst_...`. The secret is shown once, and only its hash is kept.

                    ## Reads
                    TEXT),
                ['block' => Tiles::class, 'data' => ['style' => 'routes'], 'inner' => [
                    ['block' => Tile::class, 'data' => ['label' => '/types', 'value' => 'the types you may read']],
                    ['block' => Tile::class, 'data' => ['label' => '/types/{type}', 'value' => 'one, with the schema a write takes']],
                    ['block' => Tile::class, 'data' => ['label' => '/schema', 'value' => 'every payload you may read']],
                    ['block' => Tile::class, 'data' => ['label' => '/entries/{type}', 'value' => 'a page of entries']],
                    ['block' => Tile::class, 'data' => ['label' => '/entries/{type}/{id}', 'value' => 'one entry']],
                    ['block' => Tile::class, 'data' => ['label' => '/path?uri=/blog/hello', 'value' => 'the entry at a path']],
                    ['block' => Tile::class, 'data' => ['label' => '/globals/{type}', 'value' => 'a global']],
                    ['block' => Tile::class, 'data' => ['label' => '/drafts/{id}', 'value' => 'a draft']],
                ]],
                ...$this->paragraphs(<<<'TEXT'
                    A list takes `where`, as JSON in the query layer's own shape, `sort`, `page`, `perPage` (at most 100), `locale` and `depth` (1 by default, at most 3):

                        GET /api/mainstay/entries/article?where={"publishedOn":{">":"2026-01-01"}}&sort=-publishedOn,title

                    ## Writes

                    - `POST /entries/{type}`, `PATCH` and `DELETE /entries/{type}/{id}` write live; a delete goes to the trash.
                    - `PUT /globals/{type}` writes a global.
                    - `POST /drafts`, `POST /drafts/{id}/publish` and `DELETE /drafts/{id}` work on drafts.

                    A write sends `{"data": {...}, "locale": "en"}`, a relation or an image by its id. A refusal is a 401 without a token, a 403 with one, and a 422 naming each field that did not take its value.

                    ## Payloads

                    A payload is the entry's `id`, `type`, `locale`, `uri`, `createdAt` and `updatedAt`, then its fields. Moments are RFC 3339 in UTC. An image is its alt text, focal point and a URL per size; a relation is the entry it points at as far as `depth` reaches, and its id beyond. For a TypeScript front end:

                        php artisan mainstay:typescript

                    writes `/schema` out as declarations, to `resources/types/mainstay.d.ts` unless you name a path.

                    ## MCP

                    The same operations are an MCP server at `/api/mainstay/mcp`, for a client speaking Streamable HTTP: `find-entries`, `get-entry`, `save-draft`, `publish-draft` and the rest, behind the same token. Give an agent a token that edits and does not publish, and it writes drafts for a person to put live.
                    TEXT),
            ]),
        ];
    }

    private function plugins(): array
    {
        return [
            'title' => 'Writing a plugin',
            'slug' => 'plugins',
            'summary' => 'A package\'s types, blocks, field types, admin screens, listeners and AI providers, on one class.',
            'content' => $this->blocks(...[
                ...$this->paragraphs(<<<'TEXT'
                    A plugin is a class extending `Mainstay\Plugin`. Only the plugins `config/mainstay.php` names load: installing a package switches nothing on, and nothing is switched on from the admin.

                        'plugins' => [Acme\Seo\Seo::class],

                    With routes cached, run `route:cache` again after changing the list.

                    ## The class
                    TEXT),
                ['block' => CodeSample::class, 'data' => ['caption' => 'src/Seo.php', 'code' => <<<'PHP'
                    namespace Acme\Seo;

                    use Mainstay\Content\Icon;
                    use Mainstay\Events\Published;
                    use Mainstay\Plugin;

                    #[Icon('star')]
                    class Seo extends Plugin
                    {
                        public static function handle(): string
                        {
                            return 'seo';
                        }

                        public function types(): array
                        {
                            return [SeoRedirect::class, SeoSettings::class];
                        }

                        public function views(): ?string
                        {
                            return __DIR__.'/../resources/views';
                        }

                        public function routes(): ?string
                        {
                            return __DIR__.'/../routes/admin.php';
                        }

                        public function listeners(): array
                        {
                            return [Published::class => [PurgeCache::class]];
                        }
                    }
                    PHP]],
                ...$this->paragraphs(<<<'TEXT'
                    ## The handle

                    The handle names everything the plugin takes: its views are `seo::`, its screens are under `/admin/seo`, and its capability is `use_seo`. A handle that one of the admin's screens, a view namespace, a type or another plugin already has is refused as Mainstay boots.

                    ## Types, blocks and field types

                    Its types register as yours do. A block draws from the plugin's views by naming the handle, `protected ?string $viewNamespace = 'seo';`, and is then drawn by `seo::blocks.` and its own handle; a field type names it as a `protected string`, drawn by `seo::components.fields.` and its kebab-cased name. One handle names one block across a site, so give a plugin's blocks names of their own, `SeoFaq` rather than `Faq`. A site overrides any of the views at `resources/views/vendor/seo`.

                    ## Admin screens

                    The routes file is loaded inside the admin, behind its sign-in and CSRF check, and answered only to someone holding `use_seo`, which the roles grid lists under Plugins. The route named `index` is the plugin's item in the sidebar, drawn with its `#[Icon]`. A screen's view is drawn in `<x-mainstay::admin>`, and its forms carry `@csrf`:

                        Route::get('/', [RedirectController::class, 'index'])->name('index');

                    ## Listeners

                    `listeners()` maps Mainstay's events, `Saved`, `Published`, `Deleted` and `Restored`, to listener classes. They are heard once the write commits. A listener that throws in the request does so after the content is saved, so make one that can fail a queued listener.

                    ## AI providers

                    `providers()` adds providers to the Connectors screen: see AI connectors.
                    TEXT),
                ['block' => Note::class, 'data' => ['text' => 'A plugin runs as your application does, with its access. Mainstay stops what a plugin declares from taking another\'s name, not what its code does, so install one as you would any dependency.']],
            ]),
        ];
    }

    private function aiConnectors(): array
    {
        return [
            'title' => 'AI connectors',
            'slug' => 'ai-connectors',
            'summary' => 'Connect the AI providers laravel/ai speaks from one admin screen, and prompt them from your code.',
            'content' => $this->prose(<<<'TEXT'
                Mainstay talks to AI providers through laravel/ai, so every provider it speaks is one a site can connect: OpenAI, Anthropic, Gemini, Mistral, Groq, xAI, DeepSeek, OpenRouter, Ollama and the rest. The admin's Connectors screen is open to whoever holds `manage_connectors`.

                ## The screen

                - A card per provider: what it can do, read from the laravel/ai contracts it implements, and where its key comes from.
                - A password field that saves a key encrypted with `APP_KEY` in `mainstay_connectors`, never shown again.
                - Test, which asks the provider's cheapest model for one word, or embeds one, with the key kept out of any error it reports.
                - The provider each kind of work goes to: text, images, speech, transcription, embeddings, reranking and classification.

                ## Where a key comes from

                A key in the environment, under the name laravel/ai's config reads, `ANTHROPIC_API_KEY` or `OPENAI_API_KEY`, wins over one saved on the screen. A provider's other settings, a URL or a region, are laravel/ai's config:

                    php artisan vendor:publish --tag=ai-config

                A queue worker reads the saved keys when it first prompts, so restart workers after changing one.

                ## Prompting from your code

                Code uses laravel/ai itself and reaches the provider the screen chose for that kind of work:

                    use Laravel\Ai\Image;

                    use function Laravel\Ai\agent;

                    $alt = agent('You write alt text of at most 120 characters.')
                        ->prompt('A harbour at dusk, two boats.')
                        ->text;

                    $poster = Image::of('A harbour at dusk, as a linocut')->generate();

                ## A provider from a plugin

                A plugin's `providers()` adds entries to laravel/ai's `ai.providers`, each with a `label` for the screen. One on a driver laravel/ai has is config alone; an `openai-compatible` one names its models, since they cannot be guessed:

                    public function providers(): array
                    {
                        return [
                            'together' => [
                                'label' => 'Together AI',
                                'driver' => 'openai-compatible',
                                'url' => 'https://api.together.xyz/v1',
                                'models' => ['text' => ['default' => 'meta-llama/Llama-3.3-70B-Instruct-Turbo']],
                            ],
                        ];
                    }

                A driver of its own is a closure taking the application and the provider's config and returning a laravel/ai provider. Your own `ai.providers` entry of the same name wins key by key, so `config/ai.php` can give a plugin's provider a key from the environment.

                ## Not built

                There is no AI feature in the admin itself: the screen connects providers for your code and your plugins to use.
                TEXT),
        ];
    }
}
