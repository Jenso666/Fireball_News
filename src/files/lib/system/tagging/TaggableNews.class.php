<?php

/**
 * @author    Jens Krumsieck
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\system\tagging;

use cms\data\news\TaggedNewsList;
use wcf\data\tag\Tag;
use wcf\system\tagging\ITaggable;

/**
 * Tagging implementation for news.
 */
class TaggableNews implements ITaggable
{
    /**
     * {@inheritdoc}
     */
    public function getObjectList(Tag $tag)
    {
        return new TaggedNewsList($tag);
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplateName()
    {
        return 'newsListing';
    }

    /**
     * {@inheritdoc}
     */
    public function getApplication()
    {
        return 'cms';
    }
}
