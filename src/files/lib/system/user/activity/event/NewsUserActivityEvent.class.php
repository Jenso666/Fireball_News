<?php

/**
 * @author    Jens Krumsieck
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\system\user\activity\event;

use cms\data\news\NewsList;
use wcf\system\user\activity\event\IUserActivityEvent;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Activity event for created news.
 */
class NewsUserActivityEvent extends SingletonFactory implements IUserActivityEvent
{
    /**
     * {@inheritdoc}
     */
    public function prepare(array $events)
    {
        $objectIDs = array();
        foreach ($events as $event) {
            $objectIDs[] = $event->objectID;
        }

        $newsList = new NewsList();
        $newsList->getConditionBuilder()->add('news.newsID IN (?)', array(
            $objectIDs,
        ));
        $newsList->readObjects();
        $newss = $newsList->getObjects();

        foreach ($events as $event) {
            if (isset($newss[$event->objectID])) {
                $news = $newss[$event->objectID];
                $text = WCF::getLanguage()->getDynamicVariable('wcf.user.profile.recentActivity.news', array(
                    'news' => $news,
                ));

                $event->setTitle($text);
                $event->setDescription($news->getExcerpt());
                $event->setIsAccessible();
            } else {
                $event->setIsOrphaned();
            }
        }
    }
}
