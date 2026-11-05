<?php

namespace Database\Seeders;

use App\Blocks\ArticleList;
use App\Blocks\Badges;
use App\Blocks\Button;
use App\Blocks\Buttons;
use App\Blocks\Card;
use App\Blocks\Cards;
use App\Blocks\Chapter;
use App\Blocks\Chapters;
use App\Blocks\CodeSample;
use App\Blocks\CodeTabs;
use App\Blocks\Column;
use App\Blocks\Columns;
use App\Blocks\Command;
use App\Blocks\CommandRow;
use App\Blocks\Commands;
use App\Blocks\Cta;
use App\Blocks\DepthTree;
use App\Blocks\DocsList;
use App\Blocks\DocsSection;
use App\Blocks\Feature;
use App\Blocks\Features;
use App\Blocks\Gallery;
use App\Blocks\Hero;
use App\Blocks\Highlight;
use App\Blocks\Highlights;
use App\Blocks\Image;
use App\Blocks\Lifecycle;
use App\Blocks\LocaleDemo;
use App\Blocks\Note;
use App\Blocks\Phases;
use App\Blocks\Pin;
use App\Blocks\PluginCard;
use App\Blocks\PluginGrid;
use App\Blocks\Progress;
use App\Blocks\QueryDiagram;
use App\Blocks\RoadmapCard;
use App\Blocks\Section;
use App\Blocks\Step;
use App\Blocks\Steps;
use App\Blocks\Tab;
use App\Blocks\TableColumns;
use App\Blocks\Tabs;
use App\Blocks\Terminal;
use App\Blocks\Tile;
use App\Blocks\Tiles;
use App\Content\Doc;
use App\Content\Layout;
use App\Content\Page;
use Illuminate\Database\Seeder;
use Mainstay\Facades\Mainstay;

/*
 | The site's pages, each a page of blocks, the global around them and the
 | front page. Every page is written first with its title alone, so a page's
 | blocks can lead to any other, and then given its content as an editor
 | would: a draft of the change, published.
 */
class PageSeeder extends Seeder
{
    use WritesContent;

    private const REPOSITORY = 'https://github.com/mainstaycms/mainstay';

    private const INSTALL = "composer require mainstay/cms\nphp artisan vendor:publish --tag=mainstay-assets\nphp artisan migrate\nphp artisan mainstay:user you@example.com --role=administrator";

    /* The screenshots in images/, taken of this site's own admin. */
    private const IMAGES = [
        'content-tab' => 'The Content tab of the admin: this site\'s front page drawn as the site draws it, its hero selected with the block toolbar above it, and the tree of every block beside it.',
        'block-settings' => 'The features on the Features page selected on the Content tab, their settings in a dialog listing each feature inside them, beside the tree of every block.',
        'entry-sidebar' => 'An article\'s top bar and sidebar: its status, Save draft and Publish, then its address, its author and its history.',
        'details-tab' => 'An article\'s Details tab: its title, its raw content, slug, summary, publish date, glyph and tags.',
        'entry-list' => 'The list of articles, each with its status, author and last change, with search, a status filter and the trash.',
        'media-library' => 'The media library: every image in a grid with its file name, the trash and Upload.',
        'roles' => 'The editor role: a grid of capabilities for every entry type, then the taxonomies and globals.',
        'history' => 'A page\'s history: the version live now, and the one it replaced with Restore beside it, each with who published it.',
    ];

    /** @var array<string, int> each page's id by its slug */
    private array $pages = [];

    /** @var array<string, int> each screenshot's id in the library, once uploaded */
    private array $images = [];

    public function run(): void
    {
        foreach ($this->titles() as $slug => [$title, $summary]) {
            $this->pages[$slug] = $this->published(Page::class, ['title' => $title, 'slug' => $slug, 'summary' => $summary]);
        }

        $this->revise('home', $this->home());
        $this->revise('features', $this->features());
        $this->revise('page-builder', $this->pageBuilder(), ['parent' => $this->pages['features']]);
        $this->revise('roadmap', $this->roadmap());
        $this->revise('plugins', $this->plugins());
        $this->revise('docs', $this->docs());
        $this->revise('blog', $this->blog());

        Mainstay::setFrontPage(Page::class, $this->pages['home'], overrideAccess: true);
        $this->layout();
    }

    /** @return array<string, array{0: string, 1: string}> each page's title and summary, by its slug */
    private function titles(): array
    {
        return [
            'home' => ['Mainstay, a CMS for Laravel', 'Mainstay is a CMS inside your Laravel application: content types are PHP classes, most fields are real columns, and pages are built on the page your Blade views draw or read headless over an API.'],
            'features' => ['Features', 'What Mainstay does today, from content types declared in PHP to the page builder, the API and AI connectors.'],
            'page-builder' => ['Page builder', 'Editors build a page on the page your own Blade views draw, from blocks that are PHP classes.'],
            'roadmap' => ['Roadmap', 'Where Mainstay is in its plan, and the phases still to come.'],
            'plugins' => ['Plugins', 'What a package adds to Mainstay: content types, blocks, field types, admin screens, listeners and AI providers.'],
            'docs' => ['Docs', 'How to model content in PHP, edit it in the admin and serve it from your Laravel application.'],
            'blog' => ['Blog', 'Notes from the build: why Mainstay is shaped the way it is.'],
        ];
    }

    /* A page's content, and any other field changed with it, as a draft of
       the change that is then published. */
    private function revise(string $slug, array $content, array $fields = []): void
    {
        $draft = Mainstay::drafts()->save(Page::class, ['content' => $content, ...$fields], entry: $this->pages[$slug], overrideAccess: true)->id;

        Mainstay::drafts()->publish($draft, overrideAccess: true);
    }

    /* A field relating to a page or a doc, which stores each with its type. */
    private function toPage(string $slug): array
    {
        return ['type' => Page::handle(), 'id' => $this->pages[$slug]];
    }

    private function toDoc(string $slug): array
    {
        return ['type' => Doc::handle(), 'id' => $this->idOf(Doc::class, $slug)];
    }

    /* A screenshot, uploaded the first time it is asked for: the library
       holds a file once, however many pages show it. */
    private function image(string $name): int
    {
        return $this->images[$name] ??= Mainstay::media()->upload(__DIR__."/images/{$name}.webp", alt: ['en' => self::IMAGES[$name]], overrideAccess: true)->id;
    }

    private function screenshot(string $name, ?string $caption = null, string $style = 'frame', array $pins = []): array
    {
        return [
            'block' => Image::class,
            'data' => ['image' => $this->image($name), 'caption' => $caption, 'style' => $style],
            'inner' => array_map(fn (array $pin) => ['block' => Pin::class, 'data' => array_combine(['label', 'x', 'y'], $pin)], $pins),
        ];
    }

    private function button(string $label, array|string $to, string $style = 'button'): array
    {
        $target = is_string($to) ? ['url' => $to] : ['page' => $to];

        return ['block' => Button::class, 'data' => ['label' => $label, 'style' => $style, ...$target]];
    }

    private function sample(?string $caption, string $code): array
    {
        return ['block' => CodeSample::class, 'data' => ['caption' => $caption, 'code' => $code]];
    }

    private function section(array $data, array ...$inner): array
    {
        return ['block' => Section::class, 'data' => $data, 'inner' => $inner];
    }

    private function cards(string $size, array ...$cards): array
    {
        return ['block' => Cards::class, 'data' => ['size' => $size], 'inner' => array_map(fn (array $card) => ['block' => Card::class, 'data' => $card], $cards)];
    }

    private function tiles(string $style, ?string $heading, array $tiles): array
    {
        $inner = array_map(fn (string $label, string $value) => ['block' => Tile::class, 'data' => ['label' => $label, 'value' => $value]], array_keys($tiles), $tiles);

        return ['block' => Tiles::class, 'data' => ['style' => $style, 'heading' => $heading], 'inner' => $inner];
    }

    private function steps(bool $panel, array ...$steps): array
    {
        return ['block' => Steps::class, 'data' => ['panel' => $panel], 'inner' => array_map(fn (array $step) => ['block' => Step::class, 'data' => $step], $steps)];
    }

    private function home(): array
    {
        $article = <<<'PHP'
            #[Route('/blog/{slug}')]
            #[Blocks([CodeSample::class, Note::class])]
            class Article extends Entry
            {
                #[Text(required: true, localized: true)]
                public string $slug;

                #[Textarea(required: true, localized: true)]
                public string $summary;

                #[Date(required: true)]
                public CarbonImmutable $publishedOn;

                #[Terms(of: Tag::class)]
                public array $tags;

                #[Relation(to: Article::class)]
                public array $related;
            }
            PHP;

        return $this->blocks(
            [
                'block' => Hero::class,
                'data' => [
                    'eyebrow' => 'A CMS for Laravel',
                    'heading' => "Content modelled in PHP,\nbuilt on the page",
                    'lede' => 'Content types are PHP classes and most of their fields are real columns. Editors build pages on the page. Entries are drawn by your Blade views, or read over the REST and MCP API by a front end built elsewhere.',
                    'status' => 'Early and not released yet. The API can still change between commits.',
                    'caption' => 'app/Content/Article.php (abridged)',
                    'code' => $article,
                ],
                'inner' => [
                    $this->button('Read the docs', $this->toDoc('installation')),
                    ['block' => Command::class, 'data' => ['command' => 'composer require mainstay/cms']],
                ],
            ],
            $this->screenshot('content-tab', style: 'tilt'),
            $this->section(
                ['heading' => 'What one class produces', 'lede' => 'Typed properties with attributes are the schema, the admin form, the validation rules and the type your editor autocompletes. A field is a property on a class, added in code and reviewed with it.'],
                [
                    'block' => Tabs::class,
                    'data' => ['caption' => 'app/Content/Article.php (abridged)', 'code' => $article],
                    'inner' => [
                        [
                            'block' => Tab::class,
                            'data' => ['label' => 'Tables', 'heading' => 'Tables with real columns', 'badge' => 'mainstay:sync'],
                            'inner' => [
                                $this->p(...$this->inline('A table for the type, and a `_locales` table beside it for what is translated. These are the columns this site\'s `Article` has, read from its database as the page was drawn. Tags live in a pivot of their own.')),
                                ['block' => TableColumns::class, 'data' => ['of' => 'article']],
                            ],
                        ],
                        [
                            'block' => Tab::class,
                            'data' => ['label' => 'Admin form', 'heading' => 'A form in the admin'],
                            'inner' => [
                                $this->p('Every field in the order it is declared, with its rules. A change waits as a draft until it is published.'),
                                $this->screenshot('details-tab', style: 'plain'),
                            ],
                        ],
                        [
                            'block' => Tab::class,
                            'data' => ['label' => 'Route', 'heading' => 'Served at the path its route builds'],
                            'inner' => [
                                $this->p('One catch-all answers every path after the routes your app declares, so your own routes always win. These are this site\'s.'),
                                $this->tiles('routes', null, ['/{slug}' => 'page', '/blog/{slug}' => 'article', '/blog/tag/{slug}' => 'tag', '/docs/{slug}' => 'docs.page']),
                                $this->sample('resources/views/article.blade.php', <<<'BLADE'
                                    <x-layout :title="$entry->title">
                                        <h1>{{ $entry->title }}</h1>
                                        <x-date :date="$entry->publishedOn" />
                                        {{ $entry->content }}
                                    </x-layout>
                                    BLADE),
                            ],
                        ],
                        [
                            'block' => Tab::class,
                            'data' => ['label' => 'API', 'heading' => 'The same shape over HTTP', 'badge' => '/schema'],
                            'inner' => [
                                $this->p(...$this->inline('The declaration describes the content API too. REST and MCP read and write the same fields, behind tokens, and `/schema` gives every payload as JSON Schema.')),
                                $this->sample('terminal', <<<'SHELL'
                                    curl https://example.com/api/mainstay/schema
                                    curl -H "Authorization: Bearer mst_..." \
                                      "https://example.com/api/mainstay/entries/article?sort=-publishedOn"
                                    SHELL),
                            ],
                        ],
                    ],
                ],
            ),
            $this->section(
                ['heading' => 'The page builder', 'lede' => 'Editors work on the page as your site draws it. Text is typed where it shows, blocks are added, dragged and nested, and each block\'s fields open in a dialog beside it.', 'linkLabel' => 'How the page builder works', 'link' => $this->toPage('page-builder')],
                $this->screenshot('block-settings', pins: [['Its settings, in a dialog', 55, 21], ['The blocks inside it', 45, 45]]),
            ),
            $this->section(
                ['heading' => 'The admin', 'lede' => 'Lists, forms, a media library and roles, behind a sign-in of its own. It ships built, so a host never runs a JavaScript build.'],
                [
                    'block' => Gallery::class,
                    'inner' => [
                        $this->screenshot('entry-list', 'A type\'s entries, with their status, search and the trash'),
                        $this->screenshot('media-library', 'One media library for the whole installation'),
                        $this->screenshot('roles', 'A role\'s capabilities, as a grid of your types'),
                    ],
                ],
            ),
            $this->section(
                ['heading' => 'Features', 'linkLabel' => 'All features', 'link' => $this->toPage('features')],
                [
                    'block' => Highlights::class,
                    'inner' => array_map(fn (array $highlight) => ['block' => Highlight::class, 'data' => [...$highlight, 'page' => $this->pages['features']]], [
                        ['title' => 'Content types are PHP classes', 'text' => 'The class is the schema, the form and the type your editor autocompletes.', 'anchor' => 'types'],
                        ['title' => 'Real columns, kept in step', 'text' => 'A table per type. `mainstay:sync` alters development once `MAINSTAY_SCHEMA_SYNC=true`, and `mainstay:schema:check` fails CI on drift.', 'anchor' => 'columns'],
                        ['title' => 'One query layer', 'text' => 'Templates, seeders, the admin and the API read and write through the same calls, and each asks Gate first.', 'anchor' => 'query'],
                        ['title' => 'Translated per field', 'text' => 'A title gets a value per language; a date or a price stays one.', 'anchor' => 'languages'],
                        ['title' => 'Drafts, history and the trash', 'text' => 'A change waits as a draft, and an entry\'s last 50 replaced versions can be restored.', 'anchor' => 'drafts'],
                        ['title' => 'An API and an MCP server', 'text' => 'The same reads and writes over JSON and as tools a language model calls.', 'anchor' => 'api'],
                    ]),
                ],
            ),
            $this->section(
                ['heading' => 'Roadmap'],
                ['block' => RoadmapCard::class, 'data' => ['page' => $this->pages['roadmap'], 'text' => 'From declaring a content type to its API.']],
            ),
            [
                'block' => Cta::class,
                'data' => [
                    'heading' => 'Try it in a Laravel app',
                    'text' => 'Require the package, publish its assets, run its migrations and make the first account on the server. The admin is then at `/admin`.',
                    'caption' => 'terminal',
                    'code' => self::INSTALL,
                ],
                'inner' => [$this->button('Installation guide', $this->toDoc('installation'))],
            ],
        );
    }

    private function features(): array
    {
        $feature = fn (string $anchor, string $label, string $heading, string $text, array $inner, ?array $link = null) => [
            'block' => Feature::class,
            'data' => ['anchor' => $anchor, 'label' => $label, 'heading' => $heading, 'text' => $text, ...($link === null ? [] : ['linkLabel' => $link[0], 'link' => $link[1]])],
            'inner' => $inner,
        ];

        return $this->blocks(
            [
                'block' => Hero::class,
                'data' => ['eyebrow' => 'Features', 'heading' => 'What Mainstay does today', 'lede' => 'Thirteen parts of one CMS, all running inside your own Laravel application.'],
            ],
            [
                'block' => Features::class,
                'inner' => [
                    $feature('types', 'Content types', 'Content types are PHP classes', 'Typed properties with attributes are the schema, the form and the type your editor autocompletes. The class is the type in PHP, `mainstay:typescript` writes the API\'s payloads out as TypeScript, and production gets its tables from a migration.', [
                        [
                            'block' => Columns::class,
                            'inner' => [
                                ['block' => Column::class, 'inner' => [$this->sample('app/Content/Event.php', <<<'PHP'
                                    #[Route('/events/{slug}')]
                                    class Event extends Entry
                                    {
                                        #[Text(required: true, localized: true)]
                                        public string $slug;

                                        #[Date(required: true)]
                                        public CarbonImmutable $startsOn;

                                        #[Number(min: 0)]
                                        public ?int $seats;

                                        #[Image(sizes: ['card' => [640, 360]])]
                                        public ?Media $poster;
                                    }
                                    PHP)]],
                                ['block' => Column::class, 'inner' => [$this->tiles('rows', 'One declaration, read as', [
                                    'its tables' => 'event, event_locales',
                                    'its form' => '/admin',
                                    'its rules' => 'validation',
                                    'its payload' => '/schema',
                                    'its type' => 'Event',
                                ])]],
                            ],
                        ],
                    ]),
                    $feature('columns', 'Real columns', 'Real columns, kept in step', 'Every type gets a table of its own, with real columns and constraints. `mainstay:sync` alters a development database to match once `MAINSTAY_SCHEMA_SYNC=true` is set, and `mainstay:schema:check` fails CI when a database has drifted from the classes.', [
                        [
                            'block' => Terminal::class,
                            'data' => [
                                'command' => 'php artisan mainstay:schema:check',
                                'output' => "\n   ERROR  The database does not match the declared content types.\n\n  ⇂ article.glyph: declared, and the column does not exist\n  ⇂ article.subtitle: in the database, and not declared",
                            ],
                        ],
                    ]),
                    $feature('query', 'Query layer', 'One query layer, with Gate asked first', 'Templates, seeders, the admin and the API all read and write through `Mainstay::find()`, `create()`, `update()` and `delete()`. Filters and sorting are data, not closures, so HTTP carries the same call a template makes.', [
                        ['block' => QueryDiagram::class],
                        $this->sample('resources/views/tag.blade.php', <<<'PHP'
                            $articles = Mainstay::paginate(Article::class,
                                where: ['tags' => $entry->id],
                                sort: ['-publishedOn'],
                                perPage: 10,
                                depth: 1,
                            );
                            PHP),
                    ]),
                    $feature('page-builder', 'Page builder', 'A page builder made of your classes', 'Every entry has content: text and the blocks its type allows. The Content tab is the page as your site draws it, each block\'s fields and images edited in a dialog beside it.', [
                        $this->screenshot('content-tab'),
                    ], ['How the page builder works', $this->toPage('page-builder')]),
                    $feature('routing', 'Routing', 'Served by Laravel', 'An entry is served at the path its `#[Route]` builds, through a Blade template, after every route your app declares itself. No Node on the server. To build the front end elsewhere, set `mainstay.site.enabled` to false: the app then serves no entries itself, and they are read and written over the API and MCP server behind tokens.', [
                        [
                            'block' => Columns::class,
                            'inner' => [
                                ['block' => Column::class, 'inner' => [$this->tiles('routes', null, ['/{slug}' => 'page', '/blog/{slug}' => 'article', '/docs/{slug}' => 'docs.page'])]],
                                ['block' => Column::class, 'inner' => [$this->sample('app/Content/Doc.php (abridged)', <<<'PHP'
                                    #[Route('/docs/{slug}')]
                                    #[Template('docs.page')]
                                    class Doc extends Entry
                                    {
                                        #[Text(required: true, localized: true)]
                                        public string $slug;
                                    }
                                    PHP)]],
                            ],
                        ],
                    ]),
                    $feature('languages', 'Languages', 'Translated per field', 'A field says whether it is translated. A title gets one value per language; a date or a price stays one, so the languages can never drift apart. Each language is served on a host or a path prefix of its own.', [
                        [
                            'block' => Columns::class,
                            'inner' => [
                                ['block' => Column::class, 'inner' => [['block' => LocaleDemo::class]]],
                                ['block' => Column::class, 'inner' => [$this->sample('config/mainstay.php', <<<'PHP'
                                    'locales' => [
                                        'en' => 'https://example.com',
                                        'nl' => 'https://example.com/nl',
                                    ],
                                    PHP)]],
                            ],
                        ],
                    ]),
                    $feature('images', 'Images', 'Images written once', 'One media library for the installation, with alt text in every language and a focal point. Every size a field declares is written as AVIF and WebP when a file is uploaded, as far as the server\'s GD can. A size added later is written by `mainstay:media:reprocess`, which queues the missing copies of every image.', [
                        $this->screenshot('media-library'),
                        ['block' => Badges::class, 'data' => ['items' => "JPEG, PNG, GIF and WebP in\nAVIF and WebP out\nevery declared size\na focal point\nalt text per language"]],
                    ]),
                    $feature('relations', 'Relations', 'Relations, tags and globals', 'Relations are followed to the depth a read asks for. Tags are the entries of a taxonomy, found again through a real pivot. A global holds a site\'s menu and footer.', [
                        ['block' => DepthTree::class],
                    ]),
                    $feature('drafts', 'Drafts', 'Drafts, history and the trash', 'A change waits as a draft of what differs until it is published. The last 50 versions an entry replaced can be restored (a setting, or all of them), and a deleted entry waits in the trash.', [
                        ['block' => Lifecycle::class],
                        $this->screenshot('history'),
                    ]),
                    $feature('accounts', 'Accounts', 'Accounts and roles', 'The admin has a sign-in of its own, apart from your site\'s visitors. Accounts are invited by mail, and a role\'s capabilities are a grid of the types you register.', [
                        $this->screenshot('roles'),
                    ]),
                    $feature('plugins', 'Plugins', 'Plugins, switched on by config', 'A package adds types, blocks, field types, admin screens, listeners and AI providers. Only the plugins your config names load, and their screens answer behind the admin\'s sign-in and a capability of their own.', [
                        [
                            'block' => CodeTabs::class,
                            'inner' => [
                                $this->sample('config/mainstay.php', "'plugins' => [Acme\\Seo\\Seo::class],"),
                                $this->sample('src/Seo.php', <<<'PHP'
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

                                        public function listeners(): array
                                        {
                                            return [Published::class => [PurgeCache::class]];
                                        }
                                    }
                                    PHP),
                            ],
                        ],
                    ], ['Browse plugins', $this->toPage('plugins')]),
                    $feature('api', 'API', 'An API and an MCP server', 'The same reads and writes over JSON and as tools a language model calls, behind tokens that never hold more than the account that made them. Without a token, only the types marked `#[PublicRead]` are read.', [
                        [
                            'block' => Columns::class,
                            'inner' => [
                                ['block' => Column::class, 'inner' => [$this->tiles('routes', null, [
                                    '/api/mainstay/types' => 'the types you may read',
                                    '/api/mainstay/schema' => 'every payload\'s schema',
                                    '/api/mainstay/entries/{type}' => 'a page of entries',
                                    '/api/mainstay/path?uri=' => 'the entry at an address',
                                ])]],
                                ['block' => Column::class, 'inner' => [$this->sample('terminal', <<<'SHELL'
                                    php artisan mainstay:token ada@example.com "CI build" --capability=edit_articles
                                    php artisan mainstay:typescript
                                    SHELL)]],
                            ],
                        ],
                    ], ['Read about the API', $this->toDoc('content-api')]),
                    $feature('connectors', 'AI connectors', 'AI connectors', 'Every provider laravel/ai speaks, connected on one admin screen. Code prompts with laravel/ai and reaches whatever the site connected, and a plugin adds a provider of its own.', [
                        $this->steps(
                            true,
                            ['title' => 'Connect a provider', 'text' => 'Its key from the environment, or saved on the screen and encrypted at rest.', 'badge' => 'manage_connectors'],
                            ['title' => 'Test it', 'text' => 'One word from its cheapest model, or one embedding, with the key kept out of any error.'],
                            ['title' => 'Choose the defaults', 'text' => 'The provider for text, images, speech, transcription, embeddings, reranking and classification.'],
                        ),
                    ]),
                ],
            ],
        );
    }

    private function pageBuilder(): array
    {
        return $this->blocks(
            [
                'block' => Hero::class,
                'data' => [
                    'eyebrow' => 'Page builder',
                    'heading' => 'The page builder',
                    'lede' => 'Every entry has content: text and the blocks its type allows. Editors work on the Content tab, which is the page drawn by your own Blade views. Text is typed where it shows, and a block\'s fields open beside it.',
                    'layout' => 'halves',
                ],
                'inner' => [
                    $this->button('Write your first block', $this->toDoc('writing-a-block')),
                    $this->button('All features', $this->toPage('features'), 'secondary'),
                ],
            ],
            $this->screenshot('content-tab', pins: [['The block toolbar', 25, 14], ['Typed in place', 33, 62]]),
            [
                'block' => Chapters::class,
                'inner' => [
                    [
                        'block' => Chapter::class,
                        'data' => ['heading' => 'A block is a PHP class', 'text' => 'Its fields are declared as a content type\'s are. `#[Icon]` and `#[Description]` are what the inserter lists it by, `#[Example]` is what it previews, and a Blade view at `blocks.{handle}` draws it.'],
                        'inner' => [
                            $this->sample('app/Blocks/Note.php', <<<'PHP'
                                #[Icon('quote')]
                                #[Description('A line the reader should not miss.')]
                                #[Example(['text' => 'Mainstay is not released yet.'])]
                                class Note extends Block
                                {
                                    #[Textarea(required: true)]
                                    public string $text;
                                }
                                PHP),
                            $this->sample('resources/views/blocks/note.blade.php', <<<'BLADE'
                                <div class="note">
                                    <p>@field($block, 'text')</p>
                                </div>
                                BLADE),
                        ],
                    ],
                    [
                        'block' => Chapter::class,
                        'data' => ['heading' => 'Blocks hold blocks', 'text' => '`#[Blocks]` on a block\'s class names the blocks that go inside it, and `text: true` lets text sit among them. Its view prints them with `@inner($block)`, and the editor drags blocks into and out of it.'],
                        'inner' => [
                            $this->sample('app/Blocks/Cards.php', <<<'PHP'
                                #[Icon('grid')]
                                #[Example(inner: [Card::class, Card::class])]
                                #[Blocks([Card::class])]
                                class Cards extends Block
                                {
                                    #[Select(options: ['cards' => 'Cards', 'tiles' => 'Small tiles'])]
                                    public string $size = 'cards';
                                }
                                PHP),
                            $this->screenshot('block-settings', style: 'plain'),
                        ],
                    ],
                    [
                        'block' => Chapter::class,
                        'data' => ['heading' => 'Edits wait as a draft', 'text' => 'Everything done on the page is saved as a draft of what differs. Publish puts it live in one transaction and keeps the version it replaced, which the entry\'s history restores while it is among the newest 50.'],
                        'inner' => [$this->screenshot('entry-sidebar', style: 'plain')],
                    ],
                ],
            ],
            $this->section(
                ['heading' => 'The block toolbar', 'lede' => 'Selecting a block brings its toolbar with it, floating by the block, so nothing on the page moves to make room.'],
                $this->cards(
                    'tiles',
                    ['icon' => 'drag', 'title' => 'Drag', 'text' => 'Into another block, or out of one.'],
                    ['icon' => 'move', 'title' => 'Move up, move down', 'text' => 'One place at a time.'],
                    ['icon' => 'pencil', 'title' => 'Bold, italic, code', 'text' => 'On the text selected.'],
                    ['icon' => 'settings', 'title' => 'Block settings', 'text' => 'Its fields and images, in a dialog.'],
                    ['icon' => 'duplicate', 'title' => 'Duplicate', 'text' => 'A copy beside it.'],
                    ['icon' => 'delete', 'title' => 'Delete', 'text' => 'Off the page.'],
                ),
            ),
            $this->section(
                ['heading' => 'How blocks are stored', 'lede' => 'An entry\'s content is a document in a JSON column, its blocks typed data in it and never HTML. Blade draws them on every request, so changing a view changes every page that holds the block.', 'layout' => 'side'],
                $this->sample('page_locales.content', <<<'JSON'
                    {
                        "type": "block",
                        "attrs": {
                            "id": "01K8ZQ4V7M3N2D5R6T8W9X0Y1A",
                            "type": "note",
                            "data": {
                                "text": "Mainstay is not released yet."
                            }
                        }
                    }
                    JSON),
            ),
            $this->section(
                ['heading' => 'Rules it keeps'],
                $this->cards(
                    'cards',
                    ['title' => 'One handle, one block', 'text' => 'A block\'s handle names the view that draws it across the whole site, so two blocks of one handle are refused. A plugin names its blocks its own.'],
                    ['title' => 'Removed blocks are skipped', 'text' => 'A block of a type its list no longer names is left out when it is read, so a removed block type is skipped when read.'],
                    ['title' => 'Fields read from nothing', 'text' => 'A block saved before a field was added reads it as the default its property declares, or as empty.'],
                ),
            ),
        );
    }

    private function roadmap(): array
    {
        return $this->blocks(
            [
                'block' => Hero::class,
                'data' => ['eyebrow' => 'Roadmap', 'heading' => 'Where Mainstay is, and what comes next', 'lede' => 'Mainstay is built in phases. It is not released yet, so the API can still change between commits.'],
            ],
            ['block' => Progress::class, 'data' => ['testedOn' => 'SQLite, PostgreSQL, MySQL, SQL Server']],
            ['block' => Phases::class, 'data' => ['showing' => 'next', 'heading' => 'Next', 'text' => 'The phases still to build, in plan order.']],
            ['block' => Phases::class, 'data' => ['showing' => 'done', 'heading' => 'Done', 'text' => 'Content worked from PHP first, against a real site. Identity and the admin came once the model had been proven.']],
            $this->section(
                ['heading' => 'How the plan works'],
                $this->cards(
                    'cards',
                    ['icon' => 'lines', 'title' => 'A plan, in phases', 'text' => 'Each phase says what it builds and the check that proves it works.'],
                    ['icon' => 'log', 'title' => 'A log of decisions', 'text' => 'Every choice the code cannot explain by itself is written down with its reasons and what it was weighed against.'],
                    ['icon' => 'pencil', 'title' => 'Corrections, kept', 'text' => 'When later work overturns a decision, the plan lists the correction rather than rewriting the log.'],
                ),
                [
                    'block' => Buttons::class,
                    'inner' => [
                        $this->button('Read the plan', self::REPOSITORY.'/blob/main/docs/plan.md'),
                        $this->button('Read the decisions', self::REPOSITORY.'/blob/main/docs/decisions.md', 'secondary'),
                    ],
                ],
            ),
        );
    }

    private function plugins(): array
    {
        $card = fn (string $name, string $package, string $icon, string $adds, string $text) => [
            'block' => PluginCard::class,
            'data' => ['name' => $name, 'package' => $package, 'icon' => $icon, 'adds' => $adds, 'text' => $text, 'example' => true],
        ];

        return $this->blocks(
            [
                'block' => Hero::class,
                'data' => ['eyebrow' => 'Plugins', 'heading' => 'Add types, blocks and screens from a package', 'lede' => 'A plugin brings content types, blocks, field types, admin screens, listeners and AI providers. It loads only when your config names it, so installing one switches nothing on.'],
                'inner' => [['block' => Note::class, 'data' => ['text' => 'No plugins are published yet. The cards below are examples of what a plugin can add, under a placeholder vendor name.']]],
            ],
            [
                'block' => PluginGrid::class,
                'inner' => [
                    $card('SEO', 'acme/seo', 'star', 'Content types, Admin screens, Listeners', 'Redirects and site-wide SEO settings on a screen of its own, and a cache purge whenever something is published.'),
                    $card('Forms', 'acme/forms', 'form', 'Blocks, Content types, Admin screens', 'A form block editors place on any page, and a screen in the admin to read what visitors sent.'),
                    $card('Search index', 'acme/search-index', 'search', 'Listeners', 'Keeps an external search index in step by listening to `Saved`, `Published`, `Deleted` and `Restored`.'),
                    $card('CDN purge', 'acme/cdn-purge', 'cloud', 'Listeners', 'Clears cached pages at a CDN when an entry is published, from a queued listener, so a failure never fails the save.'),
                    $card('Map field', 'acme/map-field', 'pin', 'Field types', 'A field type for a point on a map, drawn in the form by a view of its own.'),
                    $card('Video embeds', 'acme/embeds', 'video', 'Blocks', 'A block for embedding video, its poster picked from the media library.'),
                    $card('Audit log', 'acme/audit-log', 'log', 'Listeners, Admin screens', 'Records what changed and when, on a screen behind a capability of its own in the roles grid.'),
                    $card('Together AI', 'acme/together', 'spark', 'AI providers', 'Adds Together AI to the Connectors screen, through laravel/ai\'s OpenAI-compatible driver.'),
                ],
            ],
            $this->section(
                ['heading' => 'Build a plugin', 'lede' => 'Extend `Mainstay\Plugin`, give it a handle, and return the types, views, routes, listeners and AI providers the package brings.'],
                [
                    'block' => Columns::class,
                    'inner' => [
                        ['block' => Column::class, 'inner' => [
                            $this->steps(
                                false,
                                ['title' => 'The handle reserves your names', 'text' => 'Views under `seo::`, screens under `/admin/seo`, and the capability `use_seo`, which the roles grid lists under Plugins.'],
                                ['title' => 'Named in config to load', 'text' => 'Add the class to `plugins` in `config/mainstay.php`. A handle another plugin or the admin already has is refused as Mainstay boots.'],
                            ),
                            ['block' => Buttons::class, 'inner' => [$this->button('Writing a plugin', $this->toDoc('plugins'), 'link')]],
                        ]],
                        ['block' => Column::class, 'inner' => [
                            $this->sample('src/Seo.php', <<<'PHP'
                                #[Icon('star')]
                                class Seo extends Plugin
                                {
                                    public static function handle(): string
                                    {
                                        return 'seo';
                                    }

                                    public function views(): ?string
                                    {
                                        return __DIR__.'/../resources/views';
                                    }

                                    public function routes(): ?string
                                    {
                                        return __DIR__.'/../routes/admin.php';
                                    }
                                }
                                PHP),
                        ]],
                    ],
                ],
            ),
        );
    }

    private function docs(): array
    {
        $command = fn (string $command, string $text, string $badge) => ['block' => CommandRow::class, 'data' => ['command' => $command, 'text' => $text, 'badge' => $badge]];
        $section = fn (string $section, string $text) => ['block' => DocsSection::class, 'data' => ['section' => $section, 'text' => $text]];

        return $this->blocks(
            [
                'block' => Hero::class,
                'data' => [
                    'eyebrow' => 'Documentation',
                    'heading' => 'Mainstay documentation',
                    'lede' => 'How to model content in PHP, edit it in the admin and serve it from your Laravel application. Start with the installation guide.',
                    'caption' => 'terminal',
                    'code' => self::INSTALL,
                ],
                'inner' => [$this->button('Installation guide', $this->toDoc('installation'))],
            ],
            $this->section(
                ['heading' => 'Guides', 'lede' => 'Written against the current main branch.'],
                [
                    'block' => DocsList::class,
                    'inner' => [
                        $section('start', 'Install the package, declare a first type and sign in to the admin.'),
                        $section('content', 'Fields, rich text and blocks, images, and the languages a field is written in.'),
                        $section('relations', 'Entries that point at each other, tags, and globals for what every page shares.'),
                        $section('rendering', 'Routes and templates, the query layer, and the blocks your views draw.'),
                        $section('editing', 'The page builder, drafts and revisions, and who may do what.'),
                        $section('extending', 'The content API and MCP server, plugins, and AI connectors.'),
                    ],
                ],
            ),
            $this->section(
                ['heading' => 'Artisan commands', 'lede' => 'The ones you will run most, each after `php artisan`.'],
                [
                    'block' => Commands::class,
                    'inner' => [
                        $command('mainstay:user you@example.com --role=administrator', 'Make an account on the server. The first one can only be made this way.', 'server'),
                        $command('mainstay:sync', 'Alter the database to match the declared types. Off unless `MAINSTAY_SCHEMA_SYNC=true`, and it refuses to run in production.', 'development'),
                        $command('mainstay:schema:check', 'Compare the database with the declared types, and fail when they differ.', 'CI'),
                        $command('mainstay:media:reprocess', 'Queue every copy of an image its fields declare and that is not written yet.', 'new sizes'),
                        $command('mainstay:token ada@example.com "CI build" --capability=edit_articles', 'Make an API token for an account, holding the capabilities named.', 'server'),
                        $command('mainstay:typescript', 'Write the content API\'s payloads out as TypeScript declarations.', 'front end'),
                        $command('vendor:publish --tag=mainstay-config', 'Publish the config: the admin\'s and the API\'s paths, their middleware, a domain and the locales.', 'once'),
                    ],
                ],
            ),
            $this->section(
                ['heading' => 'Beyond the guides'],
                $this->cards(
                    'cards',
                    ['icon' => 'log', 'title' => 'The decisions log', 'text' => 'Why each part works the way it does, and what it was weighed against.', 'url' => self::REPOSITORY.'/blob/main/docs/decisions.md'],
                    ['icon' => 'search', 'title' => 'Found something wrong?', 'text' => 'Open an issue on GitHub with what you tried and what happened.', 'url' => self::REPOSITORY.'/issues'],
                ),
            ),
        );
    }

    private function blog(): array
    {
        return $this->blocks(
            [
                'block' => Hero::class,
                'data' => ['eyebrow' => 'Blog', 'heading' => 'Notes from the build', 'lede' => 'Why Mainstay is shaped the way it is. Each article tells one entry in its decisions log: what was chosen, and what it was weighed against.', 'layout' => 'split'],
            ],
            ['block' => ArticleList::class, 'data' => ['perPage' => 10]],
        );
    }

    /* The menu, the footer's columns and its lines, written in the site's
       one language: a global's first write names the translation it adds. */
    private function layout(): void
    {
        Mainstay::saveGlobal(Layout::class, [
            'menu' => array_map(fn (string $slug) => $this->pages[$slug], ['features', 'roadmap', 'plugins', 'docs', 'blog']),
            'repository' => self::REPOSITORY,
            'tagline' => 'A CMS for Laravel: content modelled in PHP, rendered by Blade or served headless over an API.',
            'product' => array_map(fn (string $slug) => $this->pages[$slug], ['features', 'page-builder', 'plugins', 'roadmap', 'blog']),
            'reading' => [$this->toPage('docs'), $this->toDoc('installation'), $this->toDoc('first-content-type'), $this->toDoc('content-api')],
            'copyright' => '© 2026 Mainstay. MIT licence.',
            'requirements' => 'PHP 8.3+ with GD · Laravel 13',
        ], locale: 'en', overrideAccess: true);
    }
}
