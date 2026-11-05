<?php

namespace Database\Seeders;

use App\Blocks\CodeSample;
use App\Blocks\CodeTabs;
use App\Blocks\Note;
use App\Blocks\Tile;
use App\Blocks\Tiles;
use App\Content\Article;
use App\Content\Tag;
use Illuminate\Database\Seeder;

/*
 | The blog: an article for each decision in Mainstay's decisions log worth
 | telling to someone building a site, written in the order Mainstay was
 | built. The blog lists one day's articles newest first, so the last one
 | written here is the one it shows largest.
 */
class ArticleSeeder extends Seeder
{
    use WritesContent;

    /* The day these were written. */
    private const WRITTEN_ON = '2026-11-03';

    private const DECISIONS = 'https://github.com/mainstaycms/mainstay/blob/main/docs/decisions.md#';

    private const TAGS = ['architecture' => 'Architecture', 'content' => 'Content', 'editing' => 'Editing'];

    /** @var array<string, int> each tag's id by its slug */
    private array $tags = [];

    /** @var array<string, int> each article's id by its slug, for the ones after it to relate to */
    private array $articles = [];

    public function run(): void
    {
        /* A taxonomy's terms are written live: they have no drafts. */
        foreach (self::TAGS as $slug => $title) {
            $this->tags[$slug] = $this->entry(Tag::class, ['title' => $title, 'slug' => $slug]);
        }

        $this->theClassIsTheSchema();
        $this->syncInDevelopment();
        $this->onePerLanguage();
        $this->oneQueryLayer();
        $this->oneRoute();
        $this->blocksAreData();
        $this->imagesAtUpload();
        $this->relationsAreIds();
        $this->draftsBesideLive();
        $this->capabilities();
        $this->tokens();
    }

    /*
     | One article, filed under its tag, pointing at articles written before
     | it: an entry's id exists once it is published, so a relation only ever
     | points back.
     */
    private function article(string $tag, array $related, array $data): void
    {
        $this->articles[$data['slug']] = $this->published(Article::class, [
            ...$data,
            'publishedOn' => self::WRITTEN_ON,
            'tags' => [$this->tags[$tag]],
            'related' => array_map(fn (string $slug) => $this->articles[$slug], $related),
        ]);
    }

    private function theClassIsTheSchema(): void
    {
        $this->article('architecture', [], [
            'title' => 'The class is the schema',
            'slug' => 'the-class-is-the-schema',
            'summary' => 'A Mainstay content type is a PHP class. Its typed properties and their attributes are the table, the form, the validation and the JSON Schema, so a field is added in a commit and nowhere else.',
            'glyph' => '#[Text]',
            'decision' => self::DECISIONS.'structure-is-declared-in-code-content-lives-in-the-database',
            'content' => $this->blocks(...[
                ...$this->paragraphs(<<<'TEXT'
                    Most content management systems keep their content model in the database: a screen in the admin adds a field, and the field is a row somewhere that nobody reviews. Mainstay keeps it in PHP. A content type is a class extending `Entry`, each of its fields a typed property with an attribute saying what kind of field it is, and the class is registered in a service provider. That is the whole declaration.
                    TEXT),
                ['block' => CodeSample::class, 'data' => ['caption' => 'app/Content/Article.php', 'code' => <<<'PHP'
                    #[Route('/blog/{slug}')]
                    class Article extends Entry
                    {
                        #[Text(required: true, localized: true)]
                        public string $slug;

                        #[Textarea(required: true, localized: true)]
                        public string $summary;

                        #[Date(required: true)]
                        public CarbonImmutable $publishedOn;

                        #[Text(max: 40)]
                        public ?string $glyph;

                        #[Terms(of: Tag::class)]
                        public array $tags;
                    }
                    PHP]],
                ...$this->paragraphs(<<<'TEXT'
                    `Mainstay::types([Page::class, Article::class, Tag::class])` in a service provider's `boot()` registers it. Types are registered, not discovered: there is no directory Mainstay scans and no config array of them, so a type exists because a line of code says so.

                    ## One declaration, read four ways

                    Reflection reads the class once into a field list, and everything downstream is derived from that list rather than written beside it:

                    - The database. Each field type names its column, so `#[Text(max: 40)] public ?string $glyph` is a `varchar(40)` that allows null, because the property does.
                    - Validation. The same field writes the rules, `string` and `max:40`. The limit an editor is held to and the limit the column enforces are one number, so they cannot disagree.
                    - The admin. Each field type names the Blade component that draws it, so the edit form is drawn from the class and has no definition of its own.
                    - The API. Each field type describes itself as a JSON Schema fragment. The content API serves the whole schema at `/schema`, and `mainstay:typescript` writes it out as TypeScript declarations for a front end.

                    The property's PHP type is part of the declaration too. `?string` is an optional string, `string` a value every entry has. A field type checks the property it sits on, so `#[Text] public int $count` is refused while the type is read, with a message naming the attribute, rather than failing later with a type error two frames away from anything that mentions it.

                    ## What was weighed against it

                    In PHP the class is the type. `$article->summary` is a string to an editor's autocomplete and to static analysis with no generate step, and code generation is only needed where content crosses into another language, which is what the TypeScript declarations are for.

                    The other way is the database-defined model, where a screen builds content types. It lets an editor add a field without a developer, and the model can change on a live site with no deploy. The cost is that the model lives in rows: a field can arrive on production without a review, the template that should print it is written later or never, and two environments can drift apart one click at a time. Mainstay has no `content_types` table and no screen to build a type, so a field is reviewed and ships with the template that prints it. The admin draws forms from declarations; it never authors them.

                    ## The trade

                    A field is a property on a class, so adding one is a code change, and an editor cannot add one without a developer. That is the trade Mainstay chooses. A field shows up in a code review, it ships with the template that prints it, and the database follows it, through `mainstay:sync` in development and a migration you write for production.

                    Attributes are less expressive than an arbitrary configuration object, so a nested structure points at another class instead of nesting inline. Every block ends up as its own typed class with its own fields, so a block is as checkable as an entry.

                    ## A field type of your own

                    A field type is a class extending `Field`, and the attribute on the property is an instance of it. There is no registration call: the instance is already on the property when reflection reads it, so nothing ever looks a field type up by name. A field type of your own answers the same questions the built-in ones do: its column, its rules, its JSON Schema and the component that draws it in the admin.
                    TEXT),
                ['block' => Note::class, 'data' => ['text' => 'Every entry type holds a `title` and a `content` without declaring them: the title is what the admin names an entry by, the content its text and blocks.']],
            ]),
        ]);
    }

    private function syncInDevelopment(): void
    {
        $this->article('architecture', ['the-class-is-the-schema'], [
            'title' => 'Sync in development, check in CI',
            'slug' => 'sync-in-development-check-in-ci',
            'summary' => 'Mainstay alters a development database to match the classes, and refuses to in production. There the migrations are written by hand, and a check fails the build when they and the classes disagree.',
            'glyph' => 'mainstay:schema:check',
            'decision' => self::DECISIONS.'sync-the-schema-in-development-hand-write-migrations-for-production',
            'content' => $this->prose(<<<'TEXT'
                A field is a property, and a property needs a column. There were three ways to get from one to the other: generate migrations by diffing a committed snapshot of the schema; keep every field in one JSON column and have no columns to manage at all; or sync the schema automatically in development and write the production migration by hand. Mainstay does the third, plus a command that compares a database with what the classes declare and fails when they differ.

                ## Two tables for each type

                Every registered type gets a table named after it, `article` for `Article`, and a sibling holding its translated fields, `article_locales`. The sibling is there even when nothing is translated yet, because a locale with no row is how an untranslated entry is told apart from a translated one. Both carry a `site_id`, and the main table a `deleted_at`, from the first migration: added later, either would be a migration of every table in the system.

                ## In development: mainstay:sync

                `php artisan mainstay:sync` compares the database with the declared types and alters it to match. It is built to be hard to run by accident:

                - It is off until `MAINSTAY_SCHEMA_SYNC=true` turns it on.
                - It never runs in production, whatever the flag says. A flag copied into a production `.env` by mistake is exactly the case it refuses.
                - A renamed property reads as a dropped column and an added one, and nothing in a diff can tell the two apart. So it lists every column it would drop and asks first, and declining changes nothing at all, rather than applying the half that does not drop.
                - A type change that would alter stored values is asked about in the same way. Under `--force` it asks the database instead: a stored value the new type refuses stops the sync before anything is altered.
                - Afterwards it compares again, and lists anything it could not close for you to change by hand.

                ## In production: a migration you write

                Production gets an ordinary Laravel migration. Adding the `glyph` field to `Article` is this, and nothing generates it:

                    Schema::table('article', function (Blueprint $table) {
                        $table->string('glyph', 40)->nullable();
                    });

                ## In CI: mainstay:schema:check

                `php artisan mainstay:schema:check` runs against a database built by those migrations. It builds each table as the declaration would, under a random scratch name, compares the live table with it and drops the scratch copy again, so the comparison is between what the database actually has and what it would have built, in the database's own types. A migration that forgot the column fails the build with a line naming it: `article.glyph: declared, and the column does not exist`.

                ## Why not generate the migrations

                The danger of syncing in development is that the development schema and the production schema drift apart, because one was reached by automatic alters and the other by hand, and nothing proves they agree. Detecting a mismatch is cheap. Describing the fix is the expensive part, and the drift check removes the danger for a fraction of the cost.

                Some things stay manual regardless: no diff can tell a rename from a drop and an add, or convert data when a field's type changes.

                ## Where no migration is needed

                A page's blocks are stored as data in the entry's content, so adding a field to a block changes no column. It needs a default, or a type that reads nothing as something, such as a nullable one, because the blocks stored before it was added do not hold it.
                TEXT),
        ]);
    }

    private function onePerLanguage(): void
    {
        $this->article('content', ['sync-in-development-check-in-ci', 'the-class-is-the-schema'], [
            'title' => 'One value, or one per language',
            'slug' => 'one-value-or-one-per-language',
            'summary' => 'In Mainstay a field says whether it is translated. A title gets a value per language, a date or a price keeps one, and the schema keeps them apart instead of a convention.',
            'glyph' => 'localized: true',
            'decision' => self::DECISIONS.'localization-is-per-field',
            'content' => $this->blocks(...[
                ...$this->paragraphs(<<<'TEXT'
                    There are two common shapes for content in more than one language. One gives each language a row of its own and groups the rows as translations of one entry. The other declares it per field: a field that opts in holds a value per language, and one that does not holds one value shared by all of them. Mainstay declares it per field.

                    ## The trouble with a row per language

                    When every language is its own row, every field is duplicated, the ones that must never differ included: a price, an author, a start date, a flag saying an entry is featured. Nothing in the schema can say that a field is not translated, so keeping them aligned is a convention, and a convention drifts quietly, separately in every language. Declared per field, it is a fact the schema holds.

                    ## Declared on the field
                    TEXT),
                ['block' => CodeSample::class, 'data' => ['caption' => 'app/Content/Event.php', 'code' => <<<'PHP'
                    #[Route(['en' => '/events/{slug}', 'nl' => '/evenementen/{slug}'])]
                    class Event extends Entry
                    {
                        #[Text(required: true, localized: true)]
                        public string $slug;

                        #[Date(required: true)]
                        public CarbonImmutable $startsOn;

                        #[Number(min: 0)]
                        public ?int $priceInCents;
                    }
                    PHP]],
                ...$this->paragraphs(<<<'TEXT'
                    The slug is translated, so each language has an address of its own, and the route gives each language its own pattern where a segment is translated too. The date and the price are not, so they cannot differ between languages. An entry's `title` and `content`, which every entry has, are translated already.

                    ## Two tables, one join

                    The type's table holds what is shared, and the `_locales` table beside it what is translated, one row per entry and language under a unique key on the two:
                    TEXT),
                ['block' => Tiles::class, 'data' => ['heading' => 'Where each value of an event lives', 'style' => 'rows'], 'inner' => [
                    ['block' => Tile::class, 'data' => ['label' => 'Its date, in every language', 'value' => 'event.starts_on']],
                    ['block' => Tile::class, 'data' => ['label' => 'Its price, in every language', 'value' => 'event.price_in_cents']],
                    ['block' => Tile::class, 'data' => ['label' => 'Its title, per language', 'value' => 'event_locales.title']],
                    ['block' => Tile::class, 'data' => ['label' => 'Its slug, per language', 'value' => 'event_locales.slug']],
                    ['block' => Tile::class, 'data' => ['label' => 'Its text and blocks, per language', 'value' => 'event_locales.content']],
                ]],
                ...$this->paragraphs(<<<'TEXT'
                    Every read joins the two on the language asked for, as an inner join: an entry with no row in a language is not there in that language, and is not served there. That is what having no fallback means. There is no fallback to the default language's value, and publishing and the trash act on every language at once.

                    Each language's path is a row in the lookup that routes requests, unique on the site, the language and the path, so two languages may each have an entry at `/events/opening-night` without either taking the other's.

                    ## Where each language is served

                    `mainstay.locales` in the config maps each language to where it is served: a path prefix, `['en' => '/', 'nl' => '/nl']`, or a host per language. A language and where it is served are one fact, so they are written as one. A map a request could not be told apart by is refused: two languages at one base, or some with a host and some without. So is a path another language's prefix would answer instead, when it is written, since nobody could reach it.

                    ## What stays one value

                    Some things are shared by design. An entry's tags are rows in its taxonomy's pivot, which has no language column, so an entry has the same tags in every language, and each tag translates its own title. A block's fields are translated with the content they are in, whole: a field inside a block is never translated on its own. An image keeps one file and one focal point, with alt text in every language.

                    With one language configured, the admin draws no language switch and none of this shows, and the `_locales` tables are there for the day a second one is added.
                    TEXT),
            ]),
        ]);
    }

    private function oneQueryLayer(): void
    {
        $this->article('architecture', ['the-class-is-the-schema', 'sync-in-development-check-in-ci'], [
            'title' => 'One query layer, and everything else a transport',
            'slug' => 'one-query-layer',
            'summary' => 'A Blade template, a seeder, the admin and the API all read and write content through the same few calls, which ask Laravel\'s Gate before anything else. No consumer gets a different shape, or a weaker check.',
            'glyph' => 'Mainstay::find()',
            'decision' => self::DECISIONS.'one-query-layer-with-http-as-a-transport-over-it',
            'content' => $this->blocks(...[
                ...$this->paragraphs(<<<'TEXT'
                    Where does a template get its content from? Two answers were ruled out. Eloquent models in the template give the site a code path of its own, different from the API's, and an API nothing on the site uses quietly rots. Asking the site's own API over HTTP is a round trip out of the process and back in, to read a row the process could have read.

                    So there is one query layer, and everything else goes through it. It is a local API: the call the REST endpoint takes, run against the database with no HTTP hop.

                    ## The calls
                    TEXT),
                ['block' => CodeSample::class, 'data' => ['caption' => 'app/Http/Controllers/BlogController.php', 'code' => <<<'PHP'
                    $latest = Mainstay::find(Article::class,
                        where: ['publishedOn' => ['<=' => now()->toDateString()]],
                        sort: '-publishedOn',
                        limit: 3,
                    );

                    $tagged = Mainstay::paginate(Article::class, where: ['tags' => $tag->id], perPage: 10);

                    $layout = Mainstay::global(Layout::class);
                    PHP]],
                ...$this->paragraphs(<<<'TEXT'
                    `find`, `findById` and `paginate` read, `global` reads a global, and `create`, `update` and `delete` write. A template, a seeder, an import command, the admin's forms and the API all make these calls, so there is one place where what an entry looks like as data is decided.

                    ## Data, not closures

                    `where` and `sort` are arrays and strings rather than closures over a query builder, so an HTTP query string can carry the same condition a template passes. A condition is a field and a value, or a field and operators: `=`, `!=`, `<`, `<=`, `>`, `>=`, `in` and `not_in`. Over the API the same read is `?where={"publishedOn":{"<=":"2026-11-03"}}&sort=-publishedOn`.

                    A field marked internal is refused as a filter or a sort to a reader who may not see it, so its value cannot be read back off which entries match.

                    ## Typed objects, not models

                    A read hands back the declared class, hydrated through its field types: `$article->publishedOn` is a `CarbonImmutable`, `$article->tags` a list of `Tag`. The layer is written on Laravel's query builder rather than Eloquent, because an Eloquent model would hold the row in an attribute bag, a second object beside the typed properties a template reads. The current site and the trash are scoped in the one query every read starts from, so no read can be written that forgets either.

                    ## Access is opted out of, out loud

                    Every call asks Laravel's Gate first, about Mainstay's own signed-in user and never the default guard's, which on a site with members of its own would be a visitor. Without a user, reading is open, internal fields are not, and nobody writes. Code writing on its own authority, such as a seeder or an import, says so:
                    TEXT),
                ['block' => CodeSample::class, 'data' => ['caption' => 'database/seeders/ArticleSeeder.php', 'code' => <<<'PHP'
                    Mainstay::create(Tag::class, [
                        'title' => 'Architecture',
                        'slug' => 'architecture',
                    ], overrideAccess: true);
                    PHP]],
                ...$this->paragraphs(<<<'TEXT'
                    Permissions are checked unless the caller passes `overrideAccess: true`, an argument you can search a codebase for.

                    ## Depth, the same on every path

                    `depth` says how far a read follows relations. The default is 1: the entries an entry points at are loaded, and their own relations are not. Because it is an argument of the call rather than something a transport decides, a template and an HTTP client asking the same question get data of the same shape, not one nested and one flat.

                    ## The transports

                    The admin is a transport over this layer: its forms validate what a seeder's write validates and are refused what Gate refuses. The REST API is another, and the MCP server a third, each a thin layer that finds the type, makes the call as the caller and turns the answer, or the refusal, into its own format. A rule written once in the layer holds on all of them.
                    TEXT),
            ]),
        ]);
    }

    private function oneRoute(): void
    {
        $this->article('architecture', ['one-query-layer', 'one-value-or-one-per-language'], [
            'title' => 'One route for every page',
            'slug' => 'one-route-for-every-page',
            'summary' => 'An entry\'s address is declared on its type, and one fallback route answers every GET path the site\'s own routes do not. Here is why a fallback, and what it means for the routes you write yourself.',
            'glyph' => "#[Route('/blog/{slug}')]",
            'decision' => self::DECISIONS.'public-pages-are-a-fallback-route-and-each-locale-is-served-at-a-base',
            'content' => $this->blocks(...[
                ...$this->paragraphs(<<<'TEXT'
                    Something has to connect a request for `/blog/one-route-for-every-page` to an entry and a view. One way is a route per content type in `routes/web.php`. The other is one catch-all owned by the CMS. Mainstay takes the catch-all and puts the address pattern on the type, once, with fields in it. It declares structure in PHP classes, so the pattern is an attribute.

                    ## The pattern, on the type
                    TEXT),
                ['block' => CodeSample::class, 'data' => ['caption' => 'app/Content/Guide.php', 'code' => <<<'PHP'
                    #[Route('/guides/{slug}')]
                    #[Template('guides.page', 'guides.wide')]
                    class Guide extends Entry
                    {
                        #[Text(required: true, localized: true)]
                        public string $slug;
                    }
                    PHP]],
                ...$this->paragraphs(<<<'TEXT'
                    Every save writes the entry's path into a lookup table, `uris`, holding the site, the language, the path, and the type and id it leads to, under two unique indexes: one on the site, language and path, the other on the type, id and language, so an entry has one path in each language. A request is then one indexed read, not a query of every type's table in turn, and the per-type tables keep their real columns.

                    The view is the entry's own, if it chose one of those its type lists in `#[Template]`; otherwise the type's first; and with no `#[Template]` at all, the type's handle, so `Article` renders with `resources/views/article.blade.php`. A view named and missing is an error rather than a quiet step down to another one, since that would render a page with a view nobody chose.

                    ## Why a fallback route

                    When the routing was first decided, the catch-all was to be registered last, which would have made route order something every site had to get right. Laravel's fallback routes made that unnecessary: they are matched after every other route whatever order they were registered in, cached routes included. So Mainstay's catch-all is a fallback, and a route you write always wins.

                    It is not `Route::fallback()` itself. Laravel files every `Route::fallback()` under the same path, so a site's own fallback, registered after Mainstay's, would silently replace it. Mainstay's is registered under a parameter of its own and marked as a fallback, and it is the site's fallback that never runs, unless `mainstay.site.enabled` turns public pages off.

                    That has one consequence worth knowing: a route you write beats an entry at the same path without a word. Laravel's skeleton answers `/up` for its health check, so a page slugged `up` is hidden behind it. That is the escape hatch working as intended, and it is the one clash that is not refused when the entry is written, because your controller may be reading the same entry on purpose.

                    ## What is refused when it is written

                    - A path another entry already holds. Mainstay refuses the save, on the fields that build the path, rather than appending `-2`, so the editor never looks at an address they did not choose and can pick another. The unique index decides, so two saves racing for one path cannot both win.
                    - A path another language's prefix, or one of Mainstay's own routes such as the admin or the API, would answer instead, since nobody could reach it.
                    - A slug that is not lowercase words joined by hyphens, as `Str::slug()` writes them.

                    ## One address for each page

                    The router ignores a trailing slash, so `/docs/fields/` would be a second address for `/docs/fields`. The catch-all redirects it to the path without one, as Laravel's own `.htaccess` does.

                    A path that leads to no entry is an ordinary 404, drawn by `resources/views/errors/404.blade.php` as in any Laravel application. The catch-all sets the language from the request before it looks the entry up, so the 404 is in the language of the address that was asked for.

                    An entry chosen as the front page is served at `/` in every language it has, and the page it replaces takes its own pattern's path again. Like a changed slug, choosing it changes an address every visitor uses, so it needs the `publish` capability for each entry it moves.
                    TEXT),
            ]),
        ]);
    }

    private function blocksAreData(): void
    {
        $this->article('content', ['sync-in-development-check-in-ci', 'the-class-is-the-schema'], [
            'title' => 'Blocks are data, and Blade draws them',
            'slug' => 'blocks-are-data',
            'summary' => 'A page\'s blocks are stored as data, each one a typed class with fields, inside the entry\'s content. Blade turns them into markup when the page is drawn, and never before.',
            'glyph' => '@inner($block)',
            'decision' => self::DECISIONS.'blocks-are-data-in-a-json-column-never-markup',
            'content' => $this->blocks(...[
                ...$this->paragraphs(<<<'TEXT'
                    The hard requirement is that Mainstay stores data, not markup. That rules out writing blocks as HTML comments into an entry's content, rules out rendered HTML in a rich text field, and, read strictly, rules out Markdown, which is markup with a friendlier syntax. What is stored has to be something a program can read, check and draw again in a different design.

                    ## A block is a class, and a view
                    TEXT),
                ['block' => CodeTabs::class, 'inner' => [
                    ['block' => CodeSample::class, 'data' => ['caption' => 'app/Blocks/Note.php', 'code' => <<<'PHP'
                        #[Icon('quote')]
                        #[Description('A line the reader should not miss.')]
                        #[Example(['text' => 'Mainstay is not released yet.'])]
                        class Note extends Block
                        {
                            #[Textarea(required: true)]
                            public string $text;
                        }
                        PHP]],
                    ['block' => CodeSample::class, 'data' => ['caption' => 'resources/views/blocks/note.blade.php', 'code' => <<<'BLADE'
                        <div class="note">
                            <p>@field($block, 'text')</p>
                        </div>
                        BLADE]],
                ]],
                ...$this->paragraphs(<<<'TEXT'
                    A block's fields are declared with the same attributes an entry's are, and checked, cast and described by the same field types. Its view is `blocks.` and its handle, and `@field` prints a text field escaped, the way `{{ }}` would. `#[Icon]`, `#[Description]` and `#[Example]` are for the admin: how the block is listed, and what it is previewed with when an editor adds one.

                    A content type names the blocks its content may hold with `#[Blocks([Hero::class, Features::class])]` on its class. A block that holds blocks names them the same way, with `text: true` if paragraphs and headings may sit among them, and its view prints them with `@inner($block)`.

                    ## What is stored

                    Every entry has a content: one document, in the JSON ProseMirror uses, whose top-level nodes are text and blocks. A block is a node of type `block` whose attributes are its `id`, its `type` and its `data`, with the blocks and text inside it as its content. The id is given when the block is first written and kept, which is how a block that moved is told apart from one that was replaced. Rich text inside a block is ProseMirror's JSON too: a tree of nodes, which is data.

                    ## Why not tables

                    A JSON column and relational tables both hold data, so that requirement alone did not decide it. Blocks could go in a table for each block type, with an order column and a parent key, or in a column as an ordered list of typed maps.

                    Two things Mainstay had already decided settled it. The schema is synced in development and migrated by hand in production, so tables for blocks would turn adding a field to a block into a schema change and a migration, during exactly the part of a project where blocks change most. And the constraints tables would buy go unused: the admin, the API and the site never filter a page's blocks in SQL. Top-level fields stay real columns, because a slug's uniqueness and an index on a date are used every day.

                    ## What it costs

                    - Something in PHP walks the document and writes HTML at render time. Each block is drawn by its own view, and the rest by Mainstay.
                    - The set of nodes is closed. A block whose type its field no longer lists is left out of a read, and out of the next save.
                    - A question such as which pages use the pricing block cannot be asked in SQL. If it needs to be, an index derived from the content could answer it, without storing blocks in tables.
                    - A reference stored inside a block has no foreign key behind it, which is why relations are read defensively.

                    ## Drawn by the site, edited on the page

                    Because the content is data and the site's views draw it, the admin can draw it the same way. Its Content tab is the page as the site renders it, from the form as it stands: text typed where it shows, blocks added and moved there, and a block's other fields edited in a dialog beside it. The decision was taken when the admin was expected to edit blocks as a list of forms; the editor changed, and the storage did not have to, beyond becoming one document of text and blocks instead of a list of blocks beside a body.
                    TEXT),
            ]),
        ]);
    }

    private function imagesAtUpload(): void
    {
        $this->article('content', ['blocks-are-data', 'one-value-or-one-per-language'], [
            'title' => 'Every size written once, at upload',
            'slug' => 'every-size-written-once',
            'summary' => 'Mainstay writes every size an image field declares, as AVIF and WebP, the moment a file is uploaded, and serves them as plain files. Nothing is resized on request, and nothing ever needs invalidating.',
            'glyph' => '[1600, 900]',
            'decision' => self::DECISIONS.'image-derivatives-are-generated-at-upload-and-can-be-regenerated-later',
            'content' => $this->blocks(...[
                ...$this->paragraphs(<<<'TEXT'
                    A site needs each image at a few sizes: a wide one for an article's head, a small one for a card. There are two moments to make them. One is the first time a size is asked for, with a cache in front, which needs PHP on the request path wherever images are served. The other is when the file arrives. Mainstay makes them then, writes them to disk, and serves them as plain files any web server or CDN can hand out.

                    ## Sizes are declared on the field
                    TEXT),
                ['block' => CodeSample::class, 'data' => ['caption' => 'app/Content/Article.php', 'code' => <<<'PHP'
                    #[Image(sizes: ['cover' => [1600, 900], 'card' => [640]])]
                    public ?Media $cover;
                    PHP]],
                ...$this->paragraphs(<<<'TEXT'
                    A width and a height crop the image to fill that shape around its focal point; a width alone scales it. Neither enlarges an image smaller than the size. A field has to name at least one size, since nothing could print an image with none, and the property is nullable because null is no image chosen; `required: true` is what makes every write need one.

                    In a view, `{{ $article->cover?->picture('card') }}` prints a `<picture>`: the AVIF as a source, the WebP as the `<img>`, with the alt text and the real width and height on it, loading lazily. An image that is missing prints nothing.

                    ## What happens at upload

                    - The file is recognised by its bytes, not its name: JPEG, PNG, GIF and WebP. SVG is not accepted, since an SVG can carry script.
                    - It is held once, by the hash of its bytes. Uploading the same file again is refused, naming the image already in the library.
                    - The original is kept on a private disk and never served. A camera's file carries EXIF data, its GPS position included, and the copies are written without it.
                    - Every size declared by an image field of any registered type or block is written in AVIF and in WebP, to a public disk.

                    The images are made with spatie/image on GD, chosen over Imagick on purpose: one engine everywhere means one set of tests proves what is written. Each copy is written in AVIF and WebP where GD writes them, and in the original's own format where it writes no WebP.

                    ## Names that cannot go stale

                    A copy's file name is derived from everything it is made from: the hash of the original, the size, and for a size that crops, the focal point as percentages. A cropping size is named `hash-WxH-fx-fy` and a width-only size `hash-W`. Moving the focal point writes new files under new names for the cropping sizes only. So a name that exists is a file that is right, and caches and CDNs never need invalidating.

                    ## When a declaration changes

                    Adding a size is a declaration and a backfill. `php artisan mainstay:media:reprocess` queues the copies the fields now declare and that are not written yet, so a large library is processed by a queue worker rather than in a request or a deploy. Originals are kept for good, because discarding them would make a new crop or a new size impossible.

                    ## Alt text and a focal point

                    An image in the library has alt text in every content language and a focal point, set in the admin's media library. Empty alt text marks an image as decorative. Both are data on the image, not on the field using it, so an image used on ten pages is described once.

                    An image moved to the trash, or gone, reads as an image marked missing that keeps its id, the same way a relation to a deleted entry does. A page drawing it prints nothing in its place rather than failing, and saving the entry again keeps the reference.
                    TEXT),
                ['block' => Note::class, 'data' => ['text' => 'The admin\'s own grid of the library uses a 640 pixel copy of every image, written at upload beside the sizes the fields declare.']],
            ]),
        ]);
    }

    private function relationsAreIds(): void
    {
        $this->article('content', ['blocks-are-data', 'one-query-layer'], [
            'title' => 'A relation is an id, read defensively',
            'slug' => 'a-relation-is-an-id',
            'summary' => 'Mainstay stores a relation as a plain id, with no foreign key, and a read follows it to a depth. A target that is gone reads as missing rather than failing the read.',
            'glyph' => 'depth: 1',
            'decision' => self::DECISIONS.'relations-are-plain-ids-resolved-defensively',
            'content' => $this->blocks(...[
                ...$this->paragraphs(<<<'TEXT'
                    Entries point at each other: an article at the articles to read next, a menu at its pages, a button in a block at the page it leads to. Part of what an entry holds lives in its content's JSON, and a reference stored there has no foreign key behind it, so deleting the target leaves the JSON pointing at nothing. Mainstay accepts that, everywhere, rather than treating references in JSON differently from references in columns. A relation is an id, and a read resolves it defensively.

                    ## Three ways to point
                    TEXT),
                ['block' => CodeSample::class, 'data' => ['caption' => 'app/Content/Post.php', 'code' => <<<'PHP'
                    #[Relation(to: Person::class)]
                    public ?Person $author;

                    #[Relation(to: Post::class)]
                    public array $related;

                    #[Terms(of: Tag::class)]
                    public array $tags;
                    PHP]],
                ...$this->paragraphs(<<<'TEXT'
                    A relation to one type stores the id: one in a column a `where` can filter on, a list as a JSON list of ids. A relation to several types, `to: [Page::class, Doc::class]`, stores each as its type and id, because an id alone does not say which table it is in. The property says whether it is one or a list: a nullable type or a union of the targets for one, `array` for a list, which holds none rather than null.

                    Terms are a list relation kept in rows of the taxonomy's pivot instead of a column, so the reverse question can be asked too. A tag's page is `Mainstay::find(Article::class, where: ['tags' => $tag->id])`. A global, a class extending `GlobalSet`, is the third: one per site, with no address, read with `Mainstay::global(Layout::class)`, holding things such as the menu.

                    ## Depth

                    A read follows relations to a depth. The default is 1: the entries an entry points at are loaded, and their own relations are not. `depth: 0` loads none of them and `depth: 2` goes one step further. Every target is read through the same query every read starts from, so the site, the language, the trash and Gate apply to it as to anything else read. Over the API the depth is capped, at 3 with a token and 1 without, because every step multiplies what one request makes the server write out.

                    ## Missing, not broken

                    A target the read did not load is handed back as an object of its type holding its id and nothing else, with `missing` set. That covers depth running out, a target in the trash or deleted, one with no translation in the language read, and one of a type the reader may not read. A template asks:
                    TEXT),
                ['block' => CodeSample::class, 'data' => ['caption' => 'resources/views/article.blade.php', 'code' => <<<'BLADE'
                    @foreach (collect($entry->related)->reject->missing as $article)
                        <a href="{{ $article->url() }}">{{ $article->title }}</a>
                    @endforeach
                    BLADE]],
                ...$this->paragraphs(<<<'TEXT'
                    A missing target keeps its place: written back as it was read, a list keeps the reference, so an entry restored from the trash comes back into the menus that pointed at it. A reference to a type the field no longer names is left out of the read, and out of the next save.

                    ## What it costs

                    Deleting an entry quietly breaks every page that points at it, with no warning when it is deleted and no report afterwards. Every template that follows a relation has to tolerate a missing target, and that is a rule about how templates are written, not something the storage can enforce. Deleting is at least not final: the entry goes to the trash, from which it can be restored, while its address stops answering at once.

                    The alternative considered was a relations table, written on every save from every reference found, columns and JSON alike, the same trick the path lookup already uses. It would give real foreign keys, make "what links here" a query, and let a delete warn first. It stays possible precisely because it would be derived: it can be built later by reading the content that exists, with no migration of the content itself. Until broken references are a real problem on a real site, a template that skips what is missing is cheaper.
                    TEXT),
            ]),
        ]);
    }

    private function draftsBesideLive(): void
    {
        $this->article('editing', ['a-relation-is-an-id', 'one-query-layer'], [
            'title' => 'Drafts beside what is live',
            'slug' => 'drafts-beside-what-is-live',
            'summary' => 'The entry\'s row always holds what is live, so the site reads one row. A change waits as a draft of what differs, and publishing it keeps what it replaced as a revision.',
            'glyph' => 'publish($draft->id)',
            'decision' => self::DECISIONS.'drafts-live-beside-the-entry-revisions-are-snapshots-taken-on-publish',
            'content' => $this->blocks(...[
                ...$this->paragraphs(<<<'TEXT'
                    An editor needs somewhere to work on a change before visitors see it, and a way back from a bad publish. One way is to keep every version in a versions table and work out which is live on every read. Mainstay keeps the entry's own row as what is live, always. The site's read stays the single-row select it was, with no version resolution in it, because the hot path is the public site and versioning has no business being on it.

                    ## A draft is what differs

                    A draft sits beside the entry, at most one per entry, in a table of its own, `mainstay_drafts`. Mainstay keeps one draft rather than a queue of them, so which version to show collapses to whether there is a draft. It holds only the fields that differ from what is live, so a change to the summary is a draft of the summary.
                    TEXT),
                ['block' => CodeSample::class, 'data' => ['caption' => 'php artisan tinker', 'code' => <<<'PHP'
                    $draft = Mainstay::drafts()->save(Article::class, [
                        'summary' => 'A sharper summary.',
                    ], entry: 5, overrideAccess: true);

                    Mainstay::drafts()->publish($draft->id, overrideAccess: true);
                    PHP]],
                ...$this->paragraphs(<<<'TEXT'
                    In the admin these are Save draft and Publish. They need different capabilities, editing and publishing, so a writer can be let draft and not publish. The admin's Content tab draws the page from the form as it stands; the site itself never looks at a draft.

                    ## Publishing is one transaction

                    Publishing writes the draft over the entry's row, files the version it replaces as a revision and deletes the draft, in one transaction, with no resolution logic anywhere. The query layer announces it with a `Published` event, dispatched once the transaction has committed. It is one of four: `Saved` follows a create, an update or a global's save, `Deleted` a move to the trash, and `Restored` a restore from it. A plugin that clears a cache or rebuilds something on every live change listens to all four.

                    ## Revisions

                    Every write that replaces something live files the outgoing version: the whole entry, its shared fields, every language's translated ones, its view and its terms, as one JSON snapshot in one table shared by every type, `mainstay_revisions`. Not a table per type mirroring its columns, since history is read whole and never queried by field, the same reasoning as for blocks. Each revision records who had put that version live and when it stopped being live.

                    A revision is made when content stops being live, so every stored revision was really public once, which is the only thing anyone asks to roll back to. Edits to a draft make none: there is no history of drafts, and what happened between two publishes is gone, deliberately. Full snapshots rather than diffs, so a revision is read whole and depends on none of the others. The last 50 of each entry are kept, a number `mainstay.revisions` changes, or null to keep them all.
                    TEXT),
                ['block' => CodeSample::class, 'data' => ['caption' => 'php artisan tinker', 'code' => <<<'PHP'
                    $revision = Mainstay::revisions()->of(Article::class, 5, overrideAccess: true)->first();

                    $draft = Mainstay::revisions()->restore($revision->id, overrideAccess: true);
                    PHP]],
                ...$this->paragraphs(<<<'TEXT'
                    ## Restoring is saving a draft

                    Restoring a revision makes it the entry's draft, which then leaves through Publish like any other. Restoring has no rules of its own to get wrong.

                    There is one sharp edge, and it comes straight from declaring types in code. A revision is shaped by the type as it was declared then, and classes change with deploys. An old snapshot may hold a field the type no longer declares, a language no longer configured, or a value its field now refuses. Old revisions are never migrated forward. What a restore cannot bring back is left as it is live and reported on the draft it hands back, and a field declared since keeps its live value, the revision having nothing to say about it.

                    ## The trash

                    Deleting an entry moves it to the trash, in every language at once, and takes its address out of the lookup in the same request, so the address stops answering immediately rather than resolving to a row the trash then hides. Restoring it puts it back at its address. If another entry took that address meanwhile, the restored one's slug is suffixed, since a restore has no editor typing a slug to ask. Making an entry the front page suffixes the page it replaces in the same way, when that page's own path is already taken. Emptying the trash deletes for good.

                    Tags and other terms are the exception to all of this: a taxonomy's terms are written live, with no drafts and no revisions.
                    TEXT),
            ]),
        ]);
    }

    private function capabilities(): void
    {
        $this->article('editing', ['drafts-beside-what-is-live', 'one-query-layer'], [
            'title' => 'Capabilities derived from your types',
            'slug' => 'capabilities-from-your-types',
            'summary' => 'Mainstay\'s roles hold capabilities, computed from the content types you register rather than stored. Laravel\'s Gate enforces them, through one policy for each kind of content.',
            'glyph' => 'edit_others_articles',
            'decision' => self::DECISIONS.'authorization-is-built-from-capabilities-rather-than-installed',
            'content' => $this->blocks(...[
                ...$this->paragraphs(<<<'TEXT'
                    The admin has accounts of its own, in a users table of its own behind a guard of its own, so a site's members and the people editing it never share a table or a login. What each account may do is built from capabilities: they are strings, a role is a named list of them, and a user holds one role plus exceptions of their own.

                    ## Capabilities are computed, never stored

                    Each registered type has a set of capabilities named after it, and each capability also has a form covering every type of that kind, the ones deployed later included. For the `Article` type:
                    TEXT),
                ['block' => Tiles::class, 'data' => ['heading' => 'What Article derives', 'style' => 'rows'], 'inner' => [
                    ['block' => Tile::class, 'data' => ['label' => 'Write drafts, of its own', 'value' => 'edit_articles']],
                    ['block' => Tile::class, 'data' => ['label' => 'Change one that is live', 'value' => 'edit_published_articles']],
                    ['block' => Tile::class, 'data' => ['label' => 'Change someone else\'s', 'value' => 'edit_others_articles']],
                    ['block' => Tile::class, 'data' => ['label' => 'Put a draft live', 'value' => 'publish_articles']],
                    ['block' => Tile::class, 'data' => ['label' => 'Move its own to the trash', 'value' => 'delete_articles']],
                    ['block' => Tile::class, 'data' => ['label' => 'Trash someone else\'s', 'value' => 'delete_others_articles']],
                ]],
                ...$this->paragraphs(<<<'TEXT'
                    `edit_entries` is the same capability for every entry type, so a role holding it edits a type that does not exist yet. A global has `edit` and `publish`, a taxonomy `manage`, and beside them are the media library's `upload_media` and `edit_others_media`, `manage_users` for accounts, `manage_connectors` for the AI connectors, and `use_` and a plugin's handle for each plugin with screens of its own.

                    None of these is stored. A role in `mainstay_roles` is a name and a JSON list, because roles are the part an administrator edits; the vocabulary they draw from is worked out from the registered types. Storing it would mean seeding it and then proving it still agrees with the classes, the drift problem the schema check already spends a command on. A type that is deployed adds its capabilities everywhere at once, and a role naming one that no longer exists is inert rather than broken. Mainstay ships two roles, because a package cannot know a site's types: administrator, holding `*`, and editor, holding the every-type forms. The rest are the site's to make, in a grid of the capabilities its types derive.

                    ## An entry's question, mapped to capabilities

                    Asking whether someone may change one particular entry is asking several things at once: whether they edit the type at all, whether the entry is live, whether it is theirs. In Laravel a policy is that mapping, so one policy for each kind of content does it:
                    TEXT),
                ['block' => CodeSample::class, 'data' => ['caption' => 'packages/cms/src/Policies/EntryPolicy.php', 'code' => <<<'PHP'
                    public function update(?User $user, Entry $entry): Response
                    {
                        return $this->needs($user, $entry::class, 'edit', ...(isset($entry->id) ? ['edit_published'] : []), ...$this->others($user, $entry, 'edit_others'));
                    }
                    PHP]],
                ...$this->paragraphs(<<<'TEXT'
                    An entry with an id is live, so changing it needs `edit_published` as well. One that is not the user's needs `edit_others`, and one with no owner at all counts as someone else's, which is the answer that fails closed. Each check names the type's own capability and the every-type form together, and holding either is enough.

                    ## One role, and the exceptions

                    A user holds one role, and grants and denials of their own for the exception, where a denial wins. Several roles per user would need a rule for combining them and another for their conflicts; one role with exceptions needs neither.

                    Nobody hands out a capability they do not hold. A role edited, an account invited or a token made can only carry what its maker could do themselves.

                    Everything runs through Laravel's Gate, so a site that writes its own policy for one of its types does it the way Laravel documents, with no authorisation API of Mainstay's to learn.
                    TEXT),
            ]),
        ]);
    }

    private function tokens(): void
    {
        $this->article('architecture', ['capabilities-from-your-types', 'one-query-layer', 'drafts-beside-what-is-live'], [
            'title' => 'A token holds less than its account',
            'slug' => 'a-token-holds-less-than-its-account',
            'summary' => 'The content API reads and writes over JSON and as MCP tools, behind tokens with capabilities of their own. A token never holds more than its account, and an agent given one that drafts cannot publish.',
            'glyph' => 'mst_',
            'decision' => self::DECISIONS.'a-token-carries-capabilities-of-its-own-and-mcp-is-a-third-transport',
            'content' => $this->blocks(...[
                ...$this->paragraphs(<<<'TEXT'
                    PHP calls the query layer directly. The content API calls it over HTTP, as JSON under `/api/mainstay`, and an MCP server at `/api/mainstay/mcp` calls it as tools a language model can use. All three go through the same operations, which find the type, make the call as the caller and shape the answer, so a rule written once holds on each of them. What the API adds is the question of who is calling.

                    ## Public by declaration

                    A request with no token reads only the types marked `#[PublicRead]`: their published entries or their global, never an internal field, and nothing is written. Every other type answers only to a token. It decides the API alone: the site's own pages serve every type, since the public sees those as HTML anyway.
                    TEXT),
                ['block' => CodeSample::class, 'data' => ['caption' => 'app/Content/Article.php', 'code' => <<<'PHP'
                    #[PublicRead]
                    #[Route('/blog/{slug}')]
                    class Article extends Entry
                    {
                    }
                    PHP]],
                ...$this->paragraphs(<<<'TEXT'
                    The rule for nobody is in Mainstay's Gate, not in a policy. A site's own policy that answered yes for a missing user could otherwise open a private type to the internet; this way it cannot.

                    ## A token's capabilities

                    An account makes a token from its account menu in the admin, or one is made for it on the server with `php artisan mainstay:token`, and its capabilities are chosen from what the account holds. It can never choose one the account could not hand out, the rule a role already keeps. When the token is used, the account is read with the token's list as a limit, and a capability has to be held by both. Take a capability off a role and every token of its accounts loses it at once, so a token cannot outlive what its account was trusted with. A token with no capabilities holds nothing: it reads, but never an internal field, and writes nothing; `*` is everything the account holds.

                    A token is kept in `mainstay_tokens` as the SHA-256 of its secret, which is shown once. Every secret starts with `mst_`, so a person or a secret scanner knows one when they see it.

                    ## Over HTTP

                    The REST API takes the bearer token and answers in JSON, every payload described by a JSON Schema derived from the declared classes:
                    TEXT),
                ['block' => Tiles::class, 'data' => ['style' => 'routes'], 'inner' => [
                    ['block' => Tile::class, 'data' => ['label' => '/api/mainstay/schema', 'value' => 'every payload, described']],
                    ['block' => Tile::class, 'data' => ['label' => '/api/mainstay/types', 'value' => 'the types the caller may read']],
                    ['block' => Tile::class, 'data' => ['label' => '/api/mainstay/entries/article', 'value' => 'a page of a type\'s entries']],
                    ['block' => Tile::class, 'data' => ['label' => '/api/mainstay/path?uri=/blog/one-query-layer', 'value' => 'the entry at an address']],
                    ['block' => Tile::class, 'data' => ['label' => '/api/mainstay/globals/layout', 'value' => 'a global']],
                ]],
                ...$this->paragraphs(<<<'TEXT'
                    Entries are created, changed and moved to the trash under the same paths, and drafts are saved and published under `/api/mainstay/drafts`. `php artisan mainstay:typescript` writes the schema out as TypeScript declarations for a front end. A read nests what its relations point at wherever they are pointed at, so depth multiplies what one request makes the server write: it is capped at 3 with a token and 1 without, and a page holds at most 100 entries. A mistake in the request, such as an operator `where` does not take, is a 422 naming it.

                    ## Over MCP

                    The MCP server is built on laravel/mcp rather than hand-written JSON-RPC, because the protocol keeps being revised and laravel/mcp tracks it. Its tools are the same operations: `list-types` and `describe-type`, `find-entries`, `get-entry`, `get-entry-by-path` and `get-global`, `save-draft`, `get-draft`, `publish-draft` and `discard-draft`, and `create-entry`, `update-entry`, `trash-entry` and `save-global`. They are resolved per request, so the types a tool offers are the ones its caller may use.

                    The server's instructions tell an agent to write a draft and let a person publish it, unless it was asked to publish. Capabilities make that more than advice: an agent given a token that edits and does not publish writes drafts, and its publish is refused, naming the capability it lacks. That is the safe default for letting a model help with a site's content, and it needs nothing beyond the roles and tokens every account already has.

                    ## What is not in it

                    Revisions, owners, the front page, media uploads and accounts are not in the API. Moving an entry to the trash is, but browsing or restoring the trash is not.
                    TEXT),
            ]),
        ]);
    }
}
