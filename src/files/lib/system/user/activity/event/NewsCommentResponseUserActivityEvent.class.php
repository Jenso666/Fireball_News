<?php

/**
 * @author    Jens Krumsieck
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\system\user\activity\event;

use cms\data\news\NewsList;
use wcf\data\comment\response\CommentResponseList;
use wcf\data\comment\CommentList;
use wcf\data\user\User;
use wcf\system\user\activity\event\IUserActivityEvent;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Activity event for news comment responses.
 */
class NewsCommentResponseUserActivityEvent extends SingletonFactory implements IUserActivityEvent
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

        // comments responses
        $responseList = new CommentResponseList();
        $responseList->getConditionBuilder()->add('comment_response.responseID IN (?)', array($objectIDs));
        $responseList->readObjects();
        $responses = $responseList->getObjects();

        // comments
        $commentIDs = array();
        foreach ($responses as $response) {
            $commentIDs[] = $response->commentID;
        }

        $commentList = new CommentList();
        $commentList->getConditionBuilder()->add('comment.commentID IN (?)', array($commentIDs));
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
            if (isset($responses[$event->objectID])) {
                $response = $responses[$event->objectID];

                if (isset($comments[$response->commentID])) {
                    $comment = $comments[$response->commentID];

                    if (isset($newss[$comment->objectID])) {
                        $text = WCF::getLanguage()->getDynamicVariable('wcf.user.profile.recentActivity.newsCommentResponse', array(
                            'author' => new User($comment->userID),
                            'news' => $newss[$comment->objectID],
                        ));

                        $event->setTitle($text);
                        $event->setDescription($response->getFormattedMessage());
                        $event->setIsAccessible();
                    }
                }
            } else {
                $event->setIsOrphaned();
            }
        }
    }
}
