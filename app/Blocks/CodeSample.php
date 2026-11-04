<?php

namespace App\Blocks;

use Mainstay\Content\Block;
use Mainstay\Content\Description;
use Mainstay\Content\Example;
use Mainstay\Content\Icon;
use Mainstay\Fields\Text;
use Mainstay\Fields\Textarea;

/* A code sample under the name of the file it would live in, coloured, with
   a button that copies it. */
#[Icon('code')]
#[Description('A code sample under the name of its file, coloured, with a Copy button.')]
#[Example(['caption' => 'app/Content/Article.php', 'code' => "#[Route('/blog/{slug}')]\nclass Article extends Entry\n{\n}"])]
class CodeSample extends Block
{
    /* Where the code would live, shown above it. */
    #[Text]
    public ?string $caption;

    #[Textarea(required: true)]
    public string $code;
}
