<?php

/**
 * @author    Florian Gail
 * @copyright 2014-2017 codequake.de
 * @license   LGPL
 */

namespace cms\system\user\notification\event;

use cms\data\news\News;
use wcf\system\user\notification\event\AbstractUserNotificationEvent;

/**
 * Notification event to notify news author about new comment.
 */
class NewsUpdateUserNotificationEvent extends AbstractUserNotificationEvent {
	/**
	 * {@inheritdoc}
	 */
	public function getTitle() {
		return $this->getLanguage()->get('cms.news.update.notification.title');
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function getMessage() {
		$news = new News($this->userNotificationObject->objectID);
		
		return $this->getLanguage()->getDynamicVariable('cms.news.update.notification.message', [
			'news' => $news,
			'author' => $this->author,
		]);
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function getEmailMessage($notificationType = 'instant') {
		$news = new News($this->userNotificationObject->objectID);
		
		return $this->getLanguage()->getDynamicVariable('cms.news.update.notification.mail', [
			'news' => $news,
			'author' => $this->author,
		]);
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function getLink() {
		$news = new News($this->userNotificationObject->objectID);
		
		return $news->getLink();
	}
}
