<?php

/**
 * @author    Jens Krumsieck
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\system\user\activity\event;

use cms\data\news\NewsList;
use wcf\data\comment\CommentList;
use wcf\system\user\activity\event\IUserActivityEvent;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Activity event for news comments.
 */
class NewsCommentUserActivityEvent extends SingletonFactory implements IUserActivityEvent
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

        // comments
        $commentList = new CommentList();
        $commentList->getConditionBuilder()->add('comment.commentID IN (?)', array($objectIDs));
        $commentList->readObjects();
        $comments = $commentList->getObjects();

        // get news
        $newsIDs = array();
        foreach ($comments as $comment) {
            $newsIDs[] = $comment->objectID;
        }

        $newsList = new NewsList();
        $newsList->getConditionBuilder()->add('news.newsID IN (?)', array($newsIDs));
        $newsList->readObjects();
        $newss = $newsList->getObjects();

        foreach ($events as $event) {
            if (isset($comments[$event->objectID])) {
                $comment = $comments[$event->objectID];

                if (isset($newss[$comment->objectID])) {
                    $news = $newss[$comment->objectID];
                    $text = WCF::getLanguage()->getDynamicVariable('wcf.user.profile.recentActivity.newsComment', array(
                        'news' => $news,
                    ));

                    $event->setTitle($text);
                    $event->setDescription($comment->getFormattedMessage());
                    $event->setIsAccessible();
                }
            } else {
                $event->setIsOrphaned();
            }
        }
    }
}
