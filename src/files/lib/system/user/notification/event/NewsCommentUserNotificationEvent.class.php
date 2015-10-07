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
    protected $stackable = true;

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        $count = count($this->getAuthors());
        // this notification was triggered by multiple users
        if ($count > 1) {
            return $this->getLanguage()->getDynamicVariable('cms.news.comment.notification.title.stacked', array(
                'count' => $count,
                // the number of times this notification was triggered
                'timesTriggered' => $this->notification->timesTriggered,
            ));
        }

        return $this->getLanguage()->get('cms.news.comment.notification.title');
    }

    /**
     * {@inheritdoc}
     */
    public function getMessage()
    {
        $news = new News($this->userNotificationObject->objectID);

        $authors = $this->getAuthors();
        if (count($authors) > 1) {
            if (isset($authors[0])) {
                unset($authors[0]);
            }
            $count = count($authors);

            return $this->getLanguage()->getDynamicVariable('cms.news.comment.notification.message.stacked', array(
                'news' => $news,
                'author' => $this->author,
                'authors' => array_values($authors),
                'count' => $count,
                'others' => $count - 1,
                'guestTimesTriggered' => $this->notification->guestTimesTriggered,
            ));
        }

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

        $authors = $this->getAuthors();
        if (count($authors) > 1) {
            if (isset($authors[0])) {
                unset($authors[0]);
            }
            $count = count($authors);

            return $this->getLanguage()->getDynamicVariable('cms.news.commentResponseOwner.notification.mail.stacked', array(
                'news' => $news,
                'author' => $this->author,
                'authors' => array_values($authors),
                'count' => $count,
                'others' => $count - 1,
                'notificationType' => $notificationType,
                'guestTimesTriggered' => $this->notification->guestTimesTriggered,
            ));
        }

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
