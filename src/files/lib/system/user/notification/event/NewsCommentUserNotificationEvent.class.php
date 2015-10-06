<?php

/**
 * @author    Jens Krumsieck
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\system\user\notification\event;

use cms\data\news\News;
use wcf\system\request\LinkHandler;
use wcf\system\user\notification\event\AbstractUserNotificationEvent;

/**
 * Notification event to notify news author about new comment.
 */
class NewsCommentUserNotificationEvent extends AbstractUserNotificationEvent
{
    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->getLanguage()->get('cms.news.comment.notification.title');
    }

    /**
     * {@inheritdoc}
     */
    public function getMessage()
    {
        $news = new News($this->userNotificationObject->objectID);

        return $this->getLanguage()->getDynamicVariable('cms.news.comment.notification.message', array(
            'news' => $news,
            'author' => $this->author,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getEmailMessage($notificationType = 'instant')
    {
        $news = new News($this->userNotificationObject->objectID);

        return $this->getLanguage()->getDynamicVariable('cms.news.comment.notification.mail', array(
            'news' => $news,
            'author' => $this->author,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getLink()
    {
        $news = new News($this->userNotificationObject->objectID);

        return LinkHandler::getInstance()->getLink('News', array(
            'application' => 'cms',
            'object' => $news,
        ), '#comments');
    }
}
