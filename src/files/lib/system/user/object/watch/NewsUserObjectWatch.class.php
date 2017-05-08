<?php

namespace cms\system\user\object\watch;

use cms\data\news\News;
use wcf\data\object\type\AbstractObjectTypeProcessor;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\user\object\watch\IUserObjectWatch;
use wcf\system\user\storage\UserStorageHandler;

class NewsUserObjectWatch extends AbstractObjectTypeProcessor implements IUserObjectWatch {
	/**
	 * @inheritDoc
	 */
	public function validateObjectID($objectID) {
		$news = new News($objectID);
		if (!$news->newsID) {
			throw new IllegalLinkException();
		}
		
		// check permission
		if (!$news->isVisible()) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function resetUserStorage(array $userIDs) {
		UserStorageHandler::getInstance()->reset($userIDs, 'cmsUnreadWatchedNews');
		UserStorageHandler::getInstance()->reset($userIDs, 'cmsWatchedNews');
	}
}
