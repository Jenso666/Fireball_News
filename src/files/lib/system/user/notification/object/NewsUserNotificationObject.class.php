<?php

namespace cms\system\user\notification\object;

use cms\data\news\News;
use wcf\data\DatabaseObjectDecorator;
use wcf\system\user\notification\object\IUserNotificationObject;

/**
 * @method        News                getDecoratedObject()
 * @mixin        News
 */
class NewsUserNotificationObject extends DatabaseObjectDecorator implements IUserNotificationObject {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = 'cms\\data\\news\\News';
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return $this->getDecoratedObject()->getTitle();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getURL() {
		return $this->getDecoratedObject()->getLink();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getAuthorID() {
		return $this->getDecoratedObject()->getUserID();
	}
}
