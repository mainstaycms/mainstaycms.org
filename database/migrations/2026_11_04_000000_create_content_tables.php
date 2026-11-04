<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 | The tables of the site's content types, which mainstay:sync builds in
 | development and refuses to build in production. `mainstay:schema:check`
 | holds this to the classes in app/Content: a field added there needs a
 | migration here before it deploys.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->entries('page', function (Blueprint $table) {
            $table->unsignedBigInteger('parent')->nullable();
        });
        $this->locales('page', function (Blueprint $table) {
            $table->string('title');
            $table->json('content')->nullable();
            $table->string('slug');
            $table->text('summary')->nullable();
        });

        $this->entries('article', function (Blueprint $table) {
            $table->date('published_on');
            $table->string('glyph', 40)->nullable();
            $table->string('decision', 500)->nullable();
            $table->json('related');
        });
        $this->locales('article', function (Blueprint $table) {
            $table->string('title');
            $table->json('content')->nullable();
            $table->string('slug');
            $table->text('summary');
        });

        $this->entries('tag', function () {});
        $this->locales('tag', function (Blueprint $table) {
            $table->string('title');
            $table->json('content')->nullable();
            $table->string('slug');
        });
        Schema::create('tag_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('term_id');
            $table->string('entry_type');
            $table->unsignedBigInteger('entry_id');
            $table->unsignedInteger('position');
            $table->foreign('term_id')->references('id')->on('tag');
            $table->unique(['term_id', 'entry_type', 'entry_id'], 'tag_entries_unique');
            $table->index(['entry_type', 'entry_id'], 'tag_entries_entry');
        });

        $this->entries('doc', function (Blueprint $table) {
            $table->string('section');
            $table->integer('position');
        });
        $this->locales('doc', function (Blueprint $table) {
            $table->string('title');
            $table->json('content')->nullable();
            $table->string('slug');
            $table->text('summary')->nullable();
        });

        $this->entries('phase', function (Blueprint $table) {
            $table->integer('number')->nullable();
            $table->integer('position');
            $table->boolean('done');
            $table->boolean('milestone');
        });
        $this->locales('phase', function (Blueprint $table) {
            $table->string('title');
            $table->json('content')->nullable();
            $table->text('summary');
        });

        Schema::create('layout', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_id');
            $table->string('repository', 500)->nullable();
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->unsignedBigInteger('published_by')->nullable();
            $table->datetimes();
            $table->softDeletes();
            $table->foreign('site_id')->references('id')->on('sites');
            /* A global: one row per site. */
            $table->unique('site_id');
        });
        $this->locales('layout', function (Blueprint $table) {
            $table->json('menu');
            $table->text('tagline')->nullable();
            $table->json('product');
            $table->json('reading');
            $table->string('copyright')->nullable();
            $table->string('requirements')->nullable();
        });
    }

    public function down(): void
    {
        foreach (['layout', 'phase', 'doc', 'tag', 'article', 'page'] as $type) {
            Schema::dropIfExists("{$type}_locales");

            if ($type === 'tag') {
                Schema::dropIfExists('tag_entries');
            }

            Schema::dropIfExists($type);
        }
    }

    /* An entry type's main table: its shared fields between the columns
       Mainstay keeps on every one. */
    private function entries(string $type, Closure $fields): void
    {
        Schema::create($type, function (Blueprint $table) use ($fields) {
            $table->id();
            $table->unsignedBigInteger('site_id');
            $fields($table);
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->unsignedBigInteger('published_by')->nullable();
            $table->string('template')->nullable();
            $table->datetimes();
            $table->softDeletes();
            $table->foreign('site_id')->references('id')->on('sites');
        });
    }

    /* A type's localized fields, one row per entry and locale. */
    private function locales(string $type, Closure $fields): void
    {
        Schema::create("{$type}_locales", function (Blueprint $table) use ($type, $fields) {
            $table->id();
            $table->unsignedBigInteger('parent_id');
            $table->unsignedBigInteger('site_id');
            $table->string('locale');
            $fields($table);
            $table->softDeletes();
            $table->foreign('parent_id')->references('id')->on($type);
            $table->foreign('site_id')->references('id')->on('sites');
            $table->unique(['parent_id', 'locale']);
        });
    }
};
