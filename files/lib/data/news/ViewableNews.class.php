<?php

namespace cms\data\news;

use wcf\data\label\Label;
use wcf\data\user\User;
use wcf\data\user\UserProfile;
use wcf\data\DatabaseObjectDecorator;
use wcf\system\visitTracker\VisitTracker;
use wcf\system\WCF;

/**
 * Represents a viewable news
 *
 * @author      Jens Krumsieck, Florian Gail
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
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
	 * the author's userprofile
	 * @var UserProfile
	 */
	public $userProfile = null;
	
	/**
	 * list of assigned labels
	 * @var	Label[]
	 */
	protected $labels = [];

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
		$list->setObjectIDs([$newsID]);
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
	
	/**
	 * @param \wcf\data\label\Label $label
	 */
	public function addLabel(Label $label) {
		$this->labels[$label->labelID] = $label;
	}
	
	/**
	 * @return	\wcf\data\label\Label[]
	 */
	public function getLabels() {
		return $this->labels;
	}
	
	/**
	 * @return	boolean
	 */
	public function hasLabels() {
		return count($this->labels) ? true : false;
	}
}
