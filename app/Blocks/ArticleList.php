<?php

namespace App\Blocks;

use Mainstay\Content\Block;
use Mainstay\Content\Description;
use Mainstay\Content\Example;
use Mainstay\Content\Icon;
use Mainstay\Fields\Number;

/* Every article, newest first and the newest the largest, a page at a time,
   under a filter by tag: what makes a page the blog. */
#[Icon('newspaper')]
#[Description('Every article, newest first, a page at a time, under a filter by tag: what makes a page the blog.')]
#[Example(['perPage' => 7])]
class ArticleList extends Block
{
    #[Number(min: 1, max: 24)]
    public int $perPage = 10;
}
