<?php

namespace cms\system\user\notification\event;

use cms\data\news\News;
use wcf\system\user\notification\event\AbstractUserNotificationEvent;

/**
 * Notification event to notify news author about new comment.
 *
 * @author      Florian Gail
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
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
		
		return $this->getLanguage()->getDynamicVariable('cms.news.update.notification.message', array(
			'news' => $news,
			'author' => $this->author,
		));
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function getEmailMessage($notificationType = 'instant') {
		$news = new News($this->userNotificationObject->objectID);
		
		return $this->getLanguage()->getDynamicVariable('cms.news.update.notification.mail', array(
			'news' => $news,
			'author' => $this->author,
		));
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function getLink() {
		$news = new News($this->userNotificationObject->objectID);
		
		return $news->getLink();
	}
}
