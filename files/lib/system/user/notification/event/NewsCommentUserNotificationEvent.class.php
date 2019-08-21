<?php

namespace cms\system\user\notification\event;

use cms\data\news\News;
use wcf\system\request\LinkHandler;
use wcf\system\user\notification\event\AbstractUserNotificationEvent;

/**
 * Notification event to notify news author about new comment.
 *
 * @author      Jens Krumsieck
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
 */
class NewsCommentUserNotificationEvent extends AbstractUserNotificationEvent {
	/**
	 * @inheritDoc
	 */
	protected $stackable = true;

	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		$count = count($this->getAuthors());
		// this notification was triggered by multiple users
		if ($count > 1) {
			return $this->getLanguage()->getDynamicVariable('cms.news.comment.notification.title.stacked', [
				'count' => $count,
				// the number of times this notification was triggered
				'timesTriggered' => $this->notification->timesTriggered,
			]);
		}

		return $this->getLanguage()->get('cms.news.comment.notification.title');
	}

	/**
	 * @inheritDoc
	 */
	public function getMessage() {
		$news = new News($this->userNotificationObject->objectID);

		$authors = $this->getAuthors();
		if (count($authors) > 1) {
			if (isset($authors[0])) {
				unset($authors[0]);
			}
			$count = count($authors);

			return $this->getLanguage()->getDynamicVariable('cms.news.comment.notification.message.stacked', [
				'news' => $news,
				'author' => $this->author,
				'authors' => array_values($authors),
				'count' => $count,
				'others' => $count - 1,
				'guestTimesTriggered' => $this->notification->guestTimesTriggered,
			]);
		}

		return $this->getLanguage()->getDynamicVariable('cms.news.comment.notification.message', [
			'news' => $news,
			'author' => $this->author,
		]);
	}

	/**
	 * @inheritDoc
	 */
	public function getEmailMessage($notificationType = 'instant') {
		$news = new News($this->userNotificationObject->objectID);

		$authors = $this->getAuthors();
		if (count($authors) > 1) {
			if (isset($authors[0])) {
				unset($authors[0]);
			}
			$count = count($authors);

			return $this->getLanguage()->getDynamicVariable('cms.news.commentResponseOwner.notification.mail.stacked',
				[
					'news' => $news,
					'author' => $this->author,
					'authors' => array_values($authors),
					'count' => $count,
					'others' => $count - 1,
					'notificationType' => $notificationType,
					'guestTimesTriggered' => $this->notification->guestTimesTriggered,
				]);
		}

		return $this->getLanguage()->getDynamicVariable('cms.news.comment.notification.mail', [
			'news' => $news,
			'author' => $this->author,
		]);
	}

	/**
	 * @inheritDoc
	 */
	public function getLink() {
		$news = new News($this->userNotificationObject->objectID);

		return LinkHandler::getInstance()->getLink('News', [
			'application' => 'cms',
			'object' => $news,
		], '#comments');
	}
}
