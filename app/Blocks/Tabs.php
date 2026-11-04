<?php

namespace App\Blocks;

use Mainstay\Content\Block;
use Mainstay\Content\Description;
use Mainstay\Content\Example;
use Mainstay\Content\Icon;
use Mainstay\Fields\Blocks;
use Mainstay\Fields\Text;
use Mainstay\Fields\Textarea;

/* One code sample, and beside it a tab for each thing it makes, one shown
   at a time. Without a script every tab is shown. */
#[Icon('columns')]
#[Description('A code sample, and beside it a tab for each thing it makes.')]
#[Example(['caption' => 'app/Content/Article.php', 'code' => "class Article extends Entry\n{\n}"], inner: [Tab::class])]
#[Blocks([Tab::class])]
class Tabs extends Block
{
    #[Text]
    public ?string $caption;

    #[Textarea(required: true)]
    public string $code;
}
