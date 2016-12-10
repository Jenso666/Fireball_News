<?php

/**
 * @author    Jens Krumsieck
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\system\user\notification\event;

use cms\data\news\News;
use wcf\data\comment\Comment;
use wcf\system\request\LinkHandler;
use wcf\system\user\notification\event\AbstractUserNotificationEvent;

/**
 * Notification event to notify news comment author about a new response to his comment.
 */
class NewsCommentResponseUserNotificationEvent extends AbstractUserNotificationEvent {
	/**
	 * {@inheritdoc}
	 */
	public function getTitle() {
		return $this->getLanguage()->get('cms.news.commentResponse.notification.title');
	}

	/**
	 * {@inheritdoc}
	 */
	public function getMessage() {
		$comment = new Comment($this->userNotificationObject->commentID);
		$news = new News($comment->objectID);

		return $this->getLanguage()->getDynamicVariable('cms.news.commentResponse.notification.message', array('news' => $news, 'author' => $this->author,));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getEmailMessage($notificationType = 'instant') {
		$comment = new Comment($this->userNotificationObject->commentID);
		$news = new News($comment->objectID);

		return $this->getLanguage()->getDynamicVariable('cms.news.commentResponse.notification.mail', array('news' => $news, 'author' => $this->author,));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getLink() {
		$comment = new Comment($this->userNotificationObject->commentID);
		$news = new News($comment->objectID);

		return LinkHandler::getInstance()->getLink('News', array('application' => 'cms', 'object' => $news,), '#comments');
	}
}
