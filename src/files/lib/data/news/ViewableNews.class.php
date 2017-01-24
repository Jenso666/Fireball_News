<?php

/**
 * @author    Jens Krumsieck
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\data\news;

use wcf\data\user\User;
use wcf\data\user\UserProfile;
use wcf\data\DatabaseObjectDecorator;
use wcf\system\visitTracker\VisitTracker;
use wcf\system\WCF;

/**
 * Represents a viewable news.
 */
class ViewableNews extends DatabaseObjectDecorator {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = News::class;

	/**
	 * @var integer
	 */
	protected $effectiveVisitTime = null;

	/**
	 * @var UserProfile
	 */
	public $userProfile = null;

	/**
	 * Returns when the active user visited this news.
	 *
	 * @return int
	 * @throws \wcf\system\exception\SystemException
	 */
	public function getVisitTime() {
		if ($this->effectiveVisitTime === null) {
			if (WCF::getUser()->userID) {
				$this->effectiveVisitTime = max($this->visitTime,
					VisitTracker::getInstance()->getVisitTime('de.codequake.cms.news'));
			}
			else {
				$this->effectiveVisitTime = max(VisitTracker::getInstance()->getObjectVisitTime('de.codequake.cms.news',
					$this->newsID), VisitTracker::getInstance()->getVisitTime('de.codequake.cms.news'));
			}
			if ($this->effectiveVisitTime === null) {
				$this->effectiveVisitTime = 0;
			}
		}

		return $this->effectiveVisitTime;
	}

	/**
	 * Returns if this news is new for the active user.
	 *
	 * @return bool
	 * @throws \wcf\system\exception\SystemException
	 */
	public function isNew() {
		return ($this->lastChangeTime > $this->getVisitTime());
	}

	/**
	 * @param int $newsID
	 * @return \cms\data\news\ViewableNews
	 */
	public static function getNews($newsID) {
		$list = new ViewableNewsList();
		$list->setObjectIDs(array($newsID,));
		$list->readObjects();

		return $list->search($newsID);
	}

	/**
	 * @return \wcf\data\user\UserProfile
	 */
	public function getUserProfile() {
		if ($this->userProfile === null) {
			$this->userProfile = new UserProfile(new User($this->getDecoratedObject()->userID));
		}

		return $this->userProfile;
	}

	/**
	 * @inheritDoc
	 */
	/** @noinspection PhpToStringReturnInspection */
	public function __toString() {
		return $this->getDecoratedObject()->getFormattedMessage();
	}
}
