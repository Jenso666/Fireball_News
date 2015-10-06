<?php

/**
 * @author    Jens Krumsieck
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\data\news;

use wcf\data\like\object\AbstractLikeObject;
use wcf\system\request\LinkHandler;

/**
 * Represents a likeable news.
 */
class LikeableNews extends AbstractLikeObject
{
    /**
     * {@inheritdoc}
     */
    protected static $baseClass = 'cms\data\news\News';

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->subject;
    }

    /**
     * {@inheritdoc}
     */
    public function getURL()
    {
        return LinkHandler::getInstance()->getLink('News', array(
            'application' => 'cms',
            'object' => $this->getDecoratedObject(),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getUserID()
    {
        return $this->userID;
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectID()
    {
        return $this->newsID;
    }

    /**
     * {@inheritdoc}
     */
    public function updateLikeCounter($cumulativeLikes)
    {
        // update cumulative likes
        $editor = new NewsEditor($this->getDecoratedObject());
        $editor->update(array(
            'cumulativeLikes' => $cumulativeLikes,
        ));
    }
}
