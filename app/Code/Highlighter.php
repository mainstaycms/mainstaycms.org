<?php

namespace App\Code;

use PhpToken;

/*
 | Code as HTML, its text escaped and each token worth a colour in a span of
 | its class, `t-key` and the rest, which the stylesheet colours. The text
 | is kept to the character, so what is copied is what was written.
 |
 | A code block on this site names no language, and one block can hold PHP
 | and, after a blank line, the Blade that prints it. So each group of lines
 | between blank lines is told apart by how its first line starts, and each
 | run of groups alike is coloured as one: a command, then markup -- a tag or
 | a Blade line -- and PHP otherwise, read by PHP's own tokenizer. A tag or a
 | comment running on over its next lines stays markup.
 */
final class Highlighter
{
    // ponytail: told apart by how a group starts, so a language this site does
    // not write -- CSS, JSON, JavaScript -- reads as PHP. Give Mainstay's code
    // block a language to choose when the site needs one.
    private const COMMAND = '/^\s*(?:php artisan|composer|curl) /';

    private const MARKUP = '/^\s*(?:<(?!\?php)|\{\{|\{!!|@[a-z])/i';

    /* The words PHP's tokenizer reads as names that are types or keywords. */
    private const TYPES = ['string', 'int', 'float', 'bool', 'iterable', 'mixed', 'object', 'void', 'never'];

    private const CONSTANTS = ['true', 'false', 'null'];

    public static function html(string $code): string
    {
        return implode(array_map(fn (array $run) => match ($run[0]) {
            'command' => self::command($run[1]),
            'markup' => self::markup($run[1]),
            'php' => self::php($run[1]),
        }, self::runs($code)));
    }

    /** @return list<array{0: string, 1: string}> each run of lines alike, its language first; a blank line goes with the lines above it */
    private static function runs(string $code): array
    {
        $runs = [];
        $language = 'php';
        $startsGroup = true;

        foreach (preg_split('/(?<=\n)/', $code, flags: PREG_SPLIT_NO_EMPTY) as $line) {
            $isBlank = trim($line) === '';

            if ($startsGroup && ! $isBlank) {
                $language = match (true) {
                    preg_match(self::COMMAND, $line) === 1 => 'command',
                    preg_match(self::MARKUP, $line) === 1 => 'markup',
                    default => 'php',
                };
            }

            $startsGroup = $isBlank;

            $last = array_key_last($runs);

            if ($last !== null && $runs[$last][0] === $language) {
                $runs[$last][1] .= $line;
            } else {
                $runs[] = [$language, $line];
            }
        }

        return $runs;
    }

    /*
     | PHP, as PHP's own tokenizer reads it once an opening tag is put before
     | it. An attribute, `#[Route('/blog/{slug}')]`, is coloured at its
     | brackets and its name, and its arguments as any others.
     */
    private static function php(string $code): string
    {
        $html = '';
        $previous = null;
        /* The brackets open inside the attribute being read, `#[` the first. */
        $depth = 0;

        foreach (array_slice(PhpToken::tokenize("<?php {$code}"), 1) as $token) {
            $inAttribute = $depth > 0;
            $depth = match (true) {
                $token->is(T_ATTRIBUTE) => 1,
                $inAttribute && $token->text === '[' => $depth + 1,
                $inAttribute && $token->text === ']' => $depth - 1,
                default => $depth,
            };
            $closesAttribute = $inAttribute && $depth === 0;

            $class = match (true) {
                $token->is([T_COMMENT, T_DOC_COMMENT]) => 'comment',
                $token->is([T_CONSTANT_ENCAPSED_STRING, T_ENCAPSED_AND_WHITESPACE, T_START_HEREDOC, T_END_HEREDOC]), $token->text === '"' => 'str',
                $token->is(T_VARIABLE) => 'var',
                $token->is(T_ATTRIBUTE), $closesAttribute, $previous?->is(T_ATTRIBUTE) === true => 'attr',
                $token->is([T_NAME_QUALIFIED, T_NAME_FULLY_QUALIFIED]), $token->is(T_STRING) && self::isType($token->text) => 'type',
                $token->is(T_STRING) && in_array(strtolower($token->text), self::CONSTANTS, true) => 'key',
                ! $token->is([T_STRING, T_INLINE_HTML]) && preg_match('/\A[a-z_]+\z/i', $token->text) === 1 => 'key',
                default => null,
            };

            $html .= self::span($class, $token->text);

            if (! $token->isIgnorable()) {
                $previous = $token;
            }
        }

        return $html;
    }

    /* A built-in type, or a class: a name starting with a capital that is
       not all capitals, as a constant is. */
    private static function isType(string $name): bool
    {
        return in_array(strtolower($name), self::TYPES, true) || preg_match('/\A[A-Z]\w*[a-z]/', $name) === 1;
    }

    /*
     | HTML and Blade: comments, echoes and directives -- their PHP coloured as
     | PHP -- tags, attribute names and the quoted values that hold no echo.
     | Anything else is one character of text, so every character is matched.
     | Past PCRE's limits, as a directive nested thousands of brackets deep
     | is, the code is drawn uncoloured.
     */
    private static function markup(string $code): string
    {
        return preg_replace_callback(
            '/(?<comment>\{\{--.*?--\}\}|<!--.*?-->)'
                .'|(?<open>\{\{|\{!!)(?<expression>.*?)(?<close>\}\}|!!\})'
                .'|(?<![\w.])(?<directive>@[a-z]\w*)(?:(?<gap>[ \t]*)(?<arguments>\((?:[^()]++|(?&arguments))*\)))?'
                .'|(?<bracket><\/?)(?<tag>[a-z][\w:.-]*)'
                .'|(?<attribute>[\w:.-]+)(?==)'
                .'|(?<==)(?<value>"[^"{]*"|\'[^\'{]*\')'
                .'|(?<text>.)/is',
            fn (array $match) => match (true) {
                isset($match['comment']) => self::span('comment', $match['comment']),
                isset($match['open']) => self::span('key', $match['open']).self::php($match['expression']).self::span('key', $match['close']),
                isset($match['directive']) => self::span('key', $match['directive']).e($match['gap'] ?? '').self::php($match['arguments'] ?? ''),
                isset($match['tag']) => e($match['bracket']).self::span('tag', $match['tag']),
                isset($match['attribute']) => self::span('attr', $match['attribute']),
                isset($match['value']) => self::span('str', $match['value']),
                default => e($match['text']),
            },
            $code,
            flags: PREG_UNMATCHED_AS_NULL,
        ) ?? e($code);
    }

    /* A shell command: its program, its options and its quoted arguments. */
    private static function command(string $code): string
    {
        return preg_replace_callback(
            '/^(?<indent>[ \t]*)(?<program>\S+)|(?<=\s)(?<option>--?[a-z][\w-]*)|(?<string>"[^"\n]*"|\'[^\'\n]*\')|(?<text>.)/ims',
            fn (array $match) => match (true) {
                isset($match['program']) => e($match['indent']).self::span('key', $match['program']),
                isset($match['option']) => self::span('attr', $match['option']),
                isset($match['string']) => self::span('str', $match['string']),
                default => e($match['text']),
            },
            $code,
            flags: PREG_UNMATCHED_AS_NULL,
        );
    }

    private static function span(?string $class, string $text): string
    {
        return $class === null ? e($text) : '<span class="t-'.$class.'">'.e($text).'</span>';
    }
}
