<?php

namespace App\Blocks;

use Mainstay\Content\Block;
use Mainstay\Content\Description;
use Mainstay\Content\Example;
use Mainstay\Content\Icon;
use Mainstay\Fields\Boolean;
use Mainstay\Fields\Select;
use Mainstay\Fields\Text;
use Mainstay\Fields\Textarea;

/*
 | One plugin: its name, its package, what it does and what it adds. An
 | example is marked so, and has no command to install it, since there is
 | no package to install.
 */
#[Icon('star')]
#[Description('One plugin: its name and package, what it does and what it adds.')]
#[Example(['name' => 'SEO', 'package' => 'acme/seo', 'text' => 'Redirects and site-wide settings.', 'adds' => 'Content types, Admin screens'])]
class PluginCard extends Block
{
    #[Text(required: true)]
    public string $name;

    #[Text(required: true)]
    public string $package;

    #[Select(options: Symbol::class)]
    public ?string $icon;

    #[Textarea]
    public ?string $text;

    /* What it adds, a comma between each: what the filter offers. */
    #[Text]
    public ?string $adds;

    #[Boolean]
    public bool $example = true;

    /** @return list<string> */
    public function additions(): array
    {
        return array_values(array_filter(array_map(trim(...), explode(',', (string) $this->adds))));
    }
}
