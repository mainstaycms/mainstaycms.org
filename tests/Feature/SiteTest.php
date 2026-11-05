<?php

namespace Tests\Feature;

use App\Content\Article;
use App\Content\Layout;
use App\Content\Page;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Mainstay\Auth\Gate;
use Mainstay\Auth\Invitation;
use Mainstay\Auth\Role;
use Mainstay\Auth\User;
use Mainstay\Content\Entry;
use Mainstay\Content\GlobalSet;
use Mainstay\Facades\Mainstay;
use Symfony\Component\HttpFoundation\Cookie;
use Tests\TestCase;

/*
 | Every entry the seeders write, visited at its link: the end-to-end check
 | the package's own suite cannot run.
 */
class SiteTest extends TestCase
{
    private const SITE = 'http://mainstay.localhost:8000';

    protected function setUp(): void
    {
        parent::setUp();

        /* The seeder uploads its screenshots, which would otherwise land on
           the disks the dev server serves. */
        Storage::fake('public');
        Storage::fake('local');

        $this->artisan('migrate:fresh')->assertSuccessful();
        $this->artisan('mainstay:sync', ['--force' => true])->assertSuccessful();
        $this->seed();
    }

    public function test_every_entry_renders_at_its_link(): void
    {
        $visited = 0;

        /* Every type but the global, which has no page and is read with
           Mainstay::global(), and the roadmap's phases, which have none. */
        foreach (array_filter(Mainstay::registered(), fn (string $type) => ! is_subclass_of($type, GlobalSet::class)) as $type) {
            foreach (Mainstay::find($type, depth: 0) as $entry) {
                /** @var Entry $entry */
                if ($entry->url() !== null) {
                    $this->get($entry->url())->assertOk()->assertSee('<html lang="en">', escape: false);
                    $visited++;
                }
            }
        }

        $this->assertSame(40, $visited, 'Seven pages, eleven articles, three tags and nineteen docs.');
        $this->assertSame(0, DB::table('mainstay_drafts')->count(), 'Everything seeded is published.');
    }

    public function test_the_front_page_is_drawn_from_its_blocks(): void
    {
        $this->get(self::SITE.'/')
            ->assertSeeInOrder(['<div class="prose page-content">', '<section class="hero wrap has-code">', '<figure class="image">', 'What one class produces', 'The page builder', 'The admin', 'Features', 'Roadmap', 'Try it in a Laravel app', '</div>'], escape: false)
            ->assertSee('<span class="hl"><span style="animation-delay: 0s">Content modelled in PHP,</span></span>', escape: false)
            ->assertSee('<span class="t-key">class</span> <span class="t-type">Article</span> <span class="t-key">extends</span> <span class="t-type">Entry</span>', escape: false)
            ->assertSee('<a class="btn btn-acc" href="'.self::SITE.'/docs/installation">Read the docs', escape: false)
            ->assertSee('<script src="/js/site.js" defer></script>', escape: false)
            ->assertSee('<link rel="icon" href="/favicon.svg" type="image/svg+xml">', escape: false)
            ->assertSee('<a class="feat rv"  href="'.self::SITE.'/features#query" >', escape: false)
            ->assertSee('Phases 1 to 13 are done')
            ->assertSee('<strong>The second locale and the second site</strong>', escape: false)
            ->assertSee('The admin is then at <code>/admin</code>.', escape: false);
    }

    public function test_a_table_shows_the_columns_the_database_has(): void
    {
        $page = $this->get(self::SITE.'/')->getContent();
        $table = substr($page, strpos($page, '<table class="tbl">'), 2000);

        $this->assertStringContainsString('<td class="muted">article</td><td>published_on</td><td class="muted">date</td>', $table);
        $this->assertStringContainsString('<td class="muted">article_locales</td><td>slug</td>', $table);
        $this->assertStringNotContainsString('<td>site_id</td>', $table, 'Mainstay\'s own bookkeeping is not a field.');
    }

    public function test_the_front_page_is_a_page_served_at_the_root(): void
    {
        $home = Mainstay::find(Page::class, where: ['slug' => 'home'], depth: 0)->sole();

        $this->assertSame([Page::class, $home->id], Mainstay::frontPage());
        $this->get(self::SITE.'/')->assertOk()->assertDontSee('<header class="wrap page-head">', escape: false);
        $this->get(self::SITE.'/home')->assertNotFound();
    }

    public function test_the_menu_and_the_footer_come_from_the_layout(): void
    {
        $this->get(self::SITE.'/docs')
            ->assertSeeInOrder(['<nav class="gn-links"', 'href="'.self::SITE.'/features"', 'href="'.self::SITE.'/docs"  aria-current="page"', 'href="'.self::SITE.'/blog"', '</nav>'], escape: false)
            ->assertSeeInOrder(['<h2 id="footer-product">Product</h2>', '>Page builder</a>', '<h2 id="footer-reading">Docs</h2>', '>Installation</a>', '<h2 id="footer-project">Project</h2>', 'href="https://github.com/mainstaycms/mainstay/blob/main/docs/decisions.md"'], escape: false)
            ->assertSee('PHP 8.3+ with GD · Laravel 13');
        $this->get(self::SITE.'/docs/fields')->assertSee('href="'.self::SITE.'/docs"  aria-current="true"', escape: false);
        $this->get(self::SITE.'/blog/tag/editing')->assertSee('href="'.self::SITE.'/blog"  aria-current="true"', escape: false);
        $this->get(self::SITE.'/page-builder')
            ->assertSee('href="'.self::SITE.'/features"  aria-current="true"', escape: false)
            ->assertSee('<a href="'.self::SITE.'/features">Features</a><span aria-hidden="true">/</span><span aria-current="page">Page builder</span>', escape: false);
    }

    public function test_the_blog_shows_the_newest_largest_and_pages_through_the_rest(): void
    {
        $this->get(self::SITE.'/blog')
            ->assertSeeInOrder(['<nav class="seg filters"', '>All</a>', '>Architecture</a>', '>Content</a>', '>Editing</a>', '</nav>'], escape: false)
            ->assertSee('<a class="post rv feat" href="'.self::SITE.'/blog/a-token-holds-less-than-its-account">', escape: false)
            ->assertSeeInOrder(['A token holds less than its account', 'Capabilities derived from your types', 'Drafts beside what is live'])
            ->assertDontSee('The class is the schema');
        $this->get(self::SITE.'/blog?page=2')
            ->assertSee('<title>Blog, page 2</title>', escape: false)
            ->assertSee('The class is the schema')
            ->assertDontSee('class="post rv feat"', escape: false);
    }

    public function test_a_tag_lists_only_its_articles(): void
    {
        $this->get(self::SITE.'/blog/tag/editing')
            ->assertSee('<a href="'.self::SITE.'/blog/tag/editing"  aria-current="page" >Editing</a>', escape: false)
            ->assertSee('Capabilities derived from your types')
            ->assertSee('Drafts beside what is live')
            ->assertDontSee('A token holds less than its account');
    }

    public function test_an_article_draws_its_outline_its_decision_and_more_to_read(): void
    {
        $page = $this->get(self::SITE.'/blog/a-token-holds-less-than-its-account')
            ->assertSeeInOrder(['<nav class="crumb', 'href="'.self::SITE.'/blog">Blog</a>', 'href="'.self::SITE.'/blog/tag/architecture">Architecture</a>'], escape: false)
            ->assertSee('<div class="cover acc post-cover fadein" aria-hidden="true">', escape: false)
            ->assertSee('<nav class="toc" aria-labelledby="in-this-article" data-contents>', escape: false)
            ->assertSee('<a href="#public-by-declaration">Public by declaration</a>', escape: false)
            ->assertSee('<h2 id="public-by-declaration">Public by declaration</h2>', escape: false)
            ->assertSee('href="https://github.com/mainstaycms/mainstay/blob/main/docs/decisions.md#a-token-carries-capabilities-of-its-own-and-mcp-is-a-third-transport"', escape: false)
            ->getContent();

        $more = substr($page, strpos($page, 'id="keep-reading"'));
        $this->assertSame(3, substr_count($more, '<a class="post rv"'), 'Its three related articles.');
        $this->assertSame(3, substr_count($more, '<span class="badge">'), 'Each with its tag.');
        $this->assertStringContainsString('Capabilities derived from your types', $more);
        $this->assertStringContainsString('One query layer, and everything else a transport', $more);
    }

    public function test_a_doc_has_its_sections_its_outline_and_the_next_one(): void
    {
        $this->get(self::SITE.'/docs/installation')
            ->assertSeeInOrder(['<nav class="side"', '<h2>Getting started</h2>', 'href="'.self::SITE.'/docs/installation"  aria-current="page" >Installation</a>', '<h2>Extending</h2>', '</nav>'], escape: false)
            ->assertSee('<span>Getting started</span>', escape: false)
            ->assertSee('<nav class="otp" aria-labelledby="on-this-page" data-contents>', escape: false)
            ->assertSee('<a class="card" rel="next" href="'.self::SITE.'/docs/first-content-type">', escape: false)
            ->assertDontSee('rel="prev"', escape: false)
            ->assertSee("<span class=\"t-key\">composer</span> require mainstay/cms\n<span class=\"t-key\">php</span> artisan vendor:publish <span class=\"t-attr\">--tag</span>=mainstay-assets\n<span class=\"t-key\">php</span> artisan migrate", escape: false)
            ->assertDontSee('<pre><code>', escape: false);
        $this->get(self::SITE.'/docs/ai-connectors')->assertSee('rel="prev"', escape: false)->assertDontSee('rel="next"', escape: false);
    }

    public function test_the_roadmap_counts_its_phases(): void
    {
        $this->get(self::SITE.'/roadmap')
            ->assertSee('<div class="bar16" role="img" aria-label="14 of 17 phases done"', escape: false)
            ->assertSee('<p class="stat">Phases 1 to 13</p>', escape: false)
            ->assertSeeInOrder(['>Next</h2>', 'Phase 14', 'The second locale and the second site', 'Phase 16', 'Template parts', '>Done</h2>', 'After 13', 'AI connectors', 'Phase 13', 'Phase 1', 'Declarations'], escape: false)
            ->assertSee('a <code>_locales</code> table beside it', escape: false);
        $this->assertSame(1, substr_count($this->get(self::SITE.'/roadmap')->getContent(), '>Milestone</span>'));
    }

    public function test_the_plugins_are_examples_filtered_by_what_they_add(): void
    {
        $page = $this->get(self::SITE.'/plugins')
            ->assertSee('No plugins are published yet.')
            ->assertSee('<button type="button" aria-pressed="false" data-filter="Listeners">Listeners<small>4</small></button>', escape: false)
            ->getContent();

        $this->assertSame(8, substr_count($page, '<article class="card plugin"'));
        $this->assertSame(8, substr_count($page, '<span class="badge dashed">Example</span>'));
        $this->assertStringNotContainsString('composer require acme/', $page, 'An example has nothing to install.');
    }

    public function test_the_screenshots_are_uploaded_once_and_drawn_as_pictures(): void
    {
        $this->assertSame(8, DB::table('mainstay_media')->count(), 'Each screenshot once, however many pages show it.');

        $this->get(self::SITE.'/')
            ->assertSee('<picture>', escape: false)
            ->assertSee('alt="The Content tab of the admin', escape: false);
        $this->get(self::SITE.'/page-builder')->assertSee('<span class="pin" style="left: 25%; top: 14%"><b aria-hidden="true"></b>The block toolbar</span>', escape: false);
    }

    public function test_a_draft_is_nowhere_on_the_site(): void
    {
        Mainstay::drafts()->save(Article::class, ['title' => 'Not yet', 'slug' => 'not-yet', 'summary' => 'Unpublished.', 'publishedOn' => '2026-11-03'], overrideAccess: true);

        $this->get(self::SITE.'/blog/not-yet')->assertNotFound();
        $this->get(self::SITE.'/blog')->assertDontSee('Not yet');
    }

    public function test_a_path_or_a_host_the_site_does_not_serve_is_not_found(): void
    {
        $this->get(self::SITE.'/nothing-here')->assertNotFound()->assertSee('Page not found')->assertSee('href="'.self::SITE.'/"', escape: false);
        $this->get('http://127.0.0.1:8000/')->assertNotFound();
    }

    /* A request carrying only these cookies, as a browser's next one would:
       the guards and the session forget what the last request left them. */
    private function browser(array $cookies): static
    {
        $this->app['auth']->forgetGuards();
        $this->app['session.store']->flush();
        $this->app['cookie']->flushQueuedCookies();
        $this->defaultCookies = [];
        $this->unencryptedCookies = [];

        return $this->withUnencryptedCookies($cookies);
    }

    /** @return array<string, string> */
    private function signIn(User $user, bool $remember = false): array
    {
        $response = $this->post(self::SITE.'/admin/login', ['email' => $user->email, 'password' => 'correct horse battery', 'remember' => $remember ? '1' : null]);
        $response->assertRedirect(self::SITE.'/admin')->assertSessionHasNoErrors();

        return collect($response->headers->getCookies())->mapWithKeys(fn (Cookie $cookie) => [$cookie->getName() => $cookie->getValue()])->all();
    }

    private function account(string $role): User
    {
        return User::query()->create([
            'name' => 'Ada', 'email' => "ada-{$role}@example.com", 'password' => 'correct horse battery',
            'role_id' => Role::query()->where('name', $role)->value('id'),
        ]);
    }

    public function test_an_editor_signs_in_over_http_and_reaches_the_admin(): void
    {
        $cookies = $this->signIn($this->account('editor'));

        $this->assertArrayHasKey('mainstay_session', $cookies);
        $this->browser($cookies)->get(self::SITE.'/admin')->assertOk()->assertSee('Ada');
        $this->browser([])->get(self::SITE.'/admin')->assertRedirect(self::SITE.'/admin/login');
    }

    public function test_a_signed_in_editor_is_served_the_page_a_visitor_is(): void
    {
        $visitor = $this->get(self::SITE.'/blog/drafts-beside-what-is-live')->assertOk()->getContent();
        $cookies = $this->signIn($this->account('administrator'), remember: true);

        $this->assertSame($visitor, $this->browser($cookies)->get(self::SITE.'/blog/drafts-beside-what-is-live')->assertOk()->getContent());
        $this->assertNull(Gate::user(), 'The query layer read the page as nobody, though the remember cookie was sent.');
    }

    public function test_a_new_pages_canvas_is_drawn_in_the_sites_layout(): void
    {
        $cookies = $this->signIn($this->account('administrator'));
        $content = ['type' => 'doc', 'content' => [
            ['type' => 'block', 'attrs' => ['id' => 'fresh', 'type' => 'section', 'data' => ['heading' => 'Lately']], 'content' => [
                ['type' => 'block', 'attrs' => ['id' => 'labels', 'type' => 'badges', 'data' => ['items' => 'Not yet published']]],
            ]],
        ]];

        $page = $this->browser($cookies)->post(self::SITE.'/admin/page/new/canvas', ['title' => 'Fresh', 'slug' => 'fresh', 'content' => json_encode($content)])
            ->assertOk()
            ->assertSee('<h1 class="h1">Fresh</h1>', escape: false)
            ->assertSee('<nav class="gn-links"', escape: false)
            ->assertDontSee('aria-current="page"', escape: false);

        preg_match('#<script type="application/json" data-mainstay-content>(.*?)</script>#s', $page->getContent(), $script);
        $drawn = json_decode($script[1], true);
        $this->assertSame('Lately', $drawn['doc']['content'][0]['attrs']['data']['heading']);
        $this->assertMatchesRegularExpression('#<div class="sec-body">\s*<template data-mainstay-inner></template>\s*</div>#', $drawn['blocks']['fresh']);
        $this->assertStringContainsString('data-mainstay-field="heading" data-mainstay-item="fresh"', $drawn['blocks']['fresh']);
        $this->assertStringContainsString('<span class="badge mono">Not yet published</span>', $drawn['blocks']['labels']);
    }

    public function test_the_front_pages_canvas_marks_its_blocks_text_for_typing_in_place(): void
    {
        $cookies = $this->signIn($this->account('administrator'));
        $front = Mainstay::findById(...Mainstay::frontPage());

        $page = $this->browser($cookies)->post(self::SITE."/admin/page/{$front->id}/canvas")->assertOk();
        preg_match('#<script type="application/json" data-mainstay-content>(.*?)</script>#s', $page->getContent(), $script);
        $drawn = json_decode($script[1], true);
        $hero = $drawn['doc']['content'][0];
        [$button, $command] = $hero['content'];

        /* The hero's heading typed in place, whole, and where what is inside
           it goes marked; a button's label typed in place too. */
        $this->assertSame('hero', $hero['attrs']['type']);
        $this->assertStringContainsString('data-mainstay-field="heading" data-mainstay-item="'.$hero['attrs']['id'].'"', $drawn['blocks'][$hero['attrs']['id']]);
        $this->assertMatchesRegularExpression('#<div class="actions">\s*<template data-mainstay-inner></template>\s*</div>#', $drawn['blocks'][$hero['attrs']['id']]);
        $this->assertStringContainsString('<span data-mainstay-field="label" data-mainstay-item="'.$button['attrs']['id'].'" data-mainstay-label="Label">Read the docs</span>', $drawn['blocks'][$button['attrs']['id']]);
        $this->assertSame('command', $command['attrs']['type']);

        /* The button's settings, every field of it, posted with the hero
           around it. */
        $form = $this->browser($cookies)->post(self::SITE."/admin/page/{$front->id}/block", ['block' => json_encode($button['attrs']), 'ancestors' => json_encode([$hero['attrs']])])->assertOk()->json('form');
        foreach (['label', 'page', 'url', 'style'] as $field) {
            $this->assertStringContainsString('name="data['.$field.']"', $form);
        }
    }

    public function test_a_writer_drafts_and_publishes_an_article_and_nothing_else(): void
    {
        /* As the admin's middleware leaves the request for the query layer. */
        request()->attributes->set(Gate::USER, $this->account('writer'));

        $draft = Mainstay::drafts()->save(Article::class, ['title' => 'Written', 'slug' => 'written', 'summary' => 'By a writer.', 'publishedOn' => '2026-11-03']);
        Mainstay::drafts()->publish($draft->id);

        $this->assertThrows(fn () => Mainstay::drafts()->save(Page::class, ['title' => 'Mine']), AuthorizationException::class, 'This needs edit_pages.');
        $this->assertThrows(fn () => Mainstay::saveGlobal(Layout::class, ['tagline' => 'Mine']), AuthorizationException::class, 'This needs edit_layout.');

        $this->get(self::SITE.'/blog/written')->assertOk()->assertSee('<span class="hl"><span>Written</span></span>', escape: false);
    }

    public function test_an_administrator_invites_a_writer_who_chooses_a_password_and_signs_in(): void
    {
        Notification::fake();
        $admin = $this->signIn($this->account('administrator'));

        $this->browser($admin)->post(self::SITE.'/admin/users', ['name' => 'Wren', 'email' => 'wren@example.com', 'role' => Role::query()->where('name', 'writer')->value('id')])->assertRedirect();
        $wren = User::query()->where('email', 'wren@example.com')->sole();

        $token = null;
        Notification::assertSentTo($wren, Invitation::class, function (Invitation $mail) use (&$token) {
            $token = $mail->token;

            return true;
        });

        $this->browser([])->post(self::SITE.'/admin/reset-password', ['token' => $token, 'email' => 'wren@example.com', 'password' => 'correct horse battery', 'password_confirmation' => 'correct horse battery'])
            ->assertRedirect(self::SITE.'/admin/login');

        $this->browser([]);
        $this->browser($this->signIn($wren->fresh()))->get(self::SITE.'/admin/article')->assertOk()->assertSee('A token holds less than its account');
    }
}
