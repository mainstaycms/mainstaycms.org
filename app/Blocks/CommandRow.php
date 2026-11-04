<?php

namespace App\Blocks;

use Mainstay\Content\Block;
use Mainstay\Content\Description;
use Mainstay\Content\Example;
use Mainstay\Content\Icon;
use Mainstay\Fields\Text;
use Mainstay\Fields\Textarea;

/* One Artisan command, written after `php artisan`, what it does, and when
   it is run. */
#[Icon('code')]
#[Description('One Artisan command, what it does and when it is run.')]
#[Example(['command' => 'mainstay:sync', 'text' => 'Alter the database to match the declared types.', 'badge' => 'development'])]
class CommandRow extends Block
{
    #[Text(required: true)]
    public string $command;

    #[Textarea]
    public ?string $text;

    #[Text]
    public ?string $badge;
}
