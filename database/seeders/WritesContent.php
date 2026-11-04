<?php

namespace Database\Seeders;

use Mainstay\Facades\Mainstay;

/*
 | What the site's seeders write content with, through Mainstay's query
 | layer the way an import would: on its own authority, with nobody logged
 | in. A body is a document as ProseMirror has it, built by hand here, and a
 | page's content the site's blocks, each its type, its data and what it
 | holds inside it.
 */
trait WritesContent
{
    private function entry(string $type, array $data): int
    {
        return Mainstay::create($type, $data, overrideAccess: true)->id;
    }

    /* One entry, written as an editor would: a draft, then published. */
    private function published(string $type, array $data): int
    {
        $draft = Mainstay::drafts()->save($type, $data, overrideAccess: true)->id;

        return Mainstay::drafts()->publish($draft, overrideAccess: true)->id;
    }

    /* The id of the entry of a type at a slug, written by another seeder. */
    private function idOf(string $type, string $slug): int
    {
        return Mainstay::find($type, where: ['slug' => $slug], limit: 1, depth: 0, overrideAccess: true)->firstOrFail()->id;
    }

    /*
     | A body written as plain text: a paragraph per blank line, a span in
     | backticks inside one as code, a line starting with `## ` a heading, a
     | run of lines starting with `- ` a list, and a block indented four
     | spaces as code, joined to the code block before it when only a blank
     | line came between.
     */
    private function prose(string $text): array
    {
        return $this->doc(...$this->paragraphs($text));
    }

    /** @return list<array> the nodes of a body written as plain text, as prose() reads it */
    private function paragraphs(string $text): array
    {
        $nodes = [];

        foreach (preg_split('/\R{2,}/', trim($text)) as $part) {
            $last = array_key_last($nodes);
            $isCode = str_starts_with($part, '    ');

            $nodes[] = match (true) {
                $isCode && $last !== null && $nodes[$last]['type'] === 'code_block' => $this->joined(array_pop($nodes), $part),
                $isCode => $this->pre($part),
                str_starts_with($part, '## ') => $this->h2(substr($part, 3)),
                str_starts_with($part, '- ') => $this->ul(...array_map(fn (string $item) => $this->inline(substr($item, 2)), preg_split('/\R(?=- )/', $part))),
                default => $this->p(...$this->inline($part)),
            };
        }

        return $nodes;
    }

    private function joined(array $codeBlock, string $more): array
    {
        $codeBlock['content'][0]['text'] .= "\n\n".preg_replace('/^ {4}/m', '', $more);

        return $codeBlock;
    }

    /* A paragraph's text as its nodes: plain, and each span in backticks as
       code. */
    private function inline(string $text): array
    {
        $parts = preg_split('/`([^`]+)`/', preg_replace('/\s*\R\s*/', ' ', $text), flags: PREG_SPLIT_DELIM_CAPTURE);
        $nodes = array_map(fn (string $part, int $index) => $index % 2 === 1 ? $this->code($part) : $part, $parts, array_keys($parts));

        /* A text node is never empty, as ProseMirror has it. */
        return array_values(array_filter($nodes, fn (string|array $node) => $node !== ''));
    }

    private function doc(array ...$nodes): array
    {
        return ['type' => 'doc', 'content' => $nodes];
    }

    /* Content of the site's blocks, and any text node beside them. */
    private function blocks(array ...$nodes): array
    {
        return $this->doc(...array_map(fn (array $node) => isset($node['block']) ? $this->block($node) : $node, $nodes));
    }

    /*
     | A block given as ['block' => its class, 'data' => its fields, 'inner'
     | => what it holds]: blocks so, and text as built.
     */
    private function block(array $block): array
    {
        $inner = array_map(fn (array $node) => isset($node['block']) ? $this->block($node) : $node, $block['inner'] ?? []);
        $attrs = ['type' => $block['block']::handle(), 'data' => $block['data'] ?? []];

        return ['type' => 'block', 'attrs' => $attrs, ...($inner === [] ? [] : ['content' => $inner])];
    }

    /* Text, and any node already built beside it. */
    private function p(string|array ...$inline): array
    {
        return ['type' => 'paragraph', 'content' => array_map(fn (string|array $node) => is_string($node) ? ['type' => 'text', 'text' => $node] : $node, $inline)];
    }

    private function h2(string $text): array
    {
        return ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [['type' => 'text', 'text' => $text]]];
    }

    /* One item per list of what its paragraph holds. */
    private function ul(array ...$items): array
    {
        return ['type' => 'bullet_list', 'content' => array_map(fn (array $item) => ['type' => 'list_item', 'content' => [$this->p(...$item)]], $items)];
    }

    /* A code block, without the four spaces a plain-text body indents code with. */
    private function pre(string $text): array
    {
        return ['type' => 'code_block', 'content' => [['type' => 'text', 'text' => preg_replace('/^ {4}/m', '', $text)]]];
    }

    private function code(string $text): array
    {
        return ['type' => 'text', 'text' => $text, 'marks' => [['type' => 'code']]];
    }

    private function link(string $text, string $href): array
    {
        return ['type' => 'text', 'text' => $text, 'marks' => [['type' => 'link', 'attrs' => ['href' => $href]]]];
    }
}
