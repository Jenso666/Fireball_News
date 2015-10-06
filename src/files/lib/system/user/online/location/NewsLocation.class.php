<?php

/**
 * @author    Jens Krumsieck
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\system\user\online\location;

use cms\data\news\News;
use wcf\data\user\online\UserOnline;
use wcf\system\user\online\location\IUserOnlineLocation;
use wcf\system\WCF;

/**
 * Location implementation for news entries.
 */
class NewsLocation implements IUserOnlineLocation
{
    /**
     * {@inheritdoc}
     */
    public function cache(UserOnline $user)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function get(UserOnline $user, $languageVariable = '')
    {
        $news = new News($user->objectID);
        if ($news->newsID && $news->isVisible()) {
            return WCF::getLanguage()->getDynamicVariable($languageVariable, array(
                'news' => $news,
            ));
        }

        return '';
    }
}
