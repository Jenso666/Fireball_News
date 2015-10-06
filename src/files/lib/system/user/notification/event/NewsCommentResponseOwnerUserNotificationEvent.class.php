<?php

/**
 * @author    Jens Krumsieck
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\system\user\notification\event;

use cms\data\news\News;
use wcf\data\comment\Comment;
use wcf\data\user\User;
use wcf\system\request\LinkHandler;
use wcf\system\user\notification\event\AbstractUserNotificationEvent;

/**
 * Notification event to notify news author about a new response to one of the comments to his news.
 */
class NewsCommentResponseOwnerUserNotificationEvent extends AbstractUserNotificationEvent
{
    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->getLanguage()->get('cms.news.commentResponseOwner.notification.title');
    }

    /**
     * {@inheritdoc}
     */
    public function getMessage()
    {
        $comment = new Comment($this->userNotificationObject->commentID);
        $news = new News($comment->objectID);
        $commentAuthor = new User($comment->userID);

        return $this->getLanguage()->getDynamicVariable('cms.news.commentResponseOwner.notification.message', array(
            'news' => $news,
            'author' => $this->author,
            'commentAuthor' => $commentAuthor,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getEmailMessage($notificationType = 'instant')
    {
        $comment = new Comment($this->userNotificationObject->commentID);
        $news = new News($comment->objectID);
        $commentAuthor = new User($comment->userID);

        return $this->getLanguage()->getDynamicVariable('cms.news.commentResponseOwner.notification.mail', array(
            'news' => $news,
            'author' => $this->author,
            'commentAuthor' => $commentAuthor,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getLink()
    {
        $comment = new Comment($this->userNotificationObject->commentID);
        $news = new News($comment->objectID);

        return LinkHandler::getInstance()->getLink('News', array(
            'application' => 'cms',
            'object' => $news,
        ), '#comments');
    }
}
