<?php

namespace cms\data\news;

use cms\system\label\object\NewsLabelObjectHandler;
use wcf\system\like\LikeHandler;
use wcf\system\visitTracker\VisitTracker;
use wcf\system\WCF;

/**
 * Represents a list of viewable news.
 *
 * @author      Jens Krumsieck, Florian Gail
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
 *
 * @property \cms\data\news\ViewableNews[] $objects
 */
class ViewableNewsList extends NewsList {
	/**
	 * @inheritDoc
	 */
	public $decoratorClassName = ViewableNews::class;

	/**
	 * @inheritDoc
	 */
	public $sqlOrderBy = 'news.time DESC';

	/**
	 * @inheritDoc
	 */
	public function __construct() {
		parent::__construct();

		if (WCF::getUser()->userID != 0) {
			// last visit time
			if (!empty($this->sqlSelects)) {
				$this->sqlSelects .= ',';
			}

			$this->sqlSelects .= 'tracked_visit.visitTime';
			$this->sqlJoins .= ' LEFT JOIN wcf' . WCF_N . '_tracked_visit tracked_visit ON (tracked_visit.objectTypeID = ' . VisitTracker::getInstance()->getObjectTypeID('de.codequake.cms.news') . ' AND tracked_visit.objectID = news.newsID AND tracked_visit.userID = ' . WCF::getUser()->userID . ')';
		}

		// get like status
		if (!empty($this->sqlSelects)) {
			$this->sqlSelects .= ',';
		}

		$this->sqlSelects .= 'like_object.likes, like_object.dislikes';
		$this->sqlJoins .= ' LEFT JOIN wcf' . WCF_N . '_like_object like_object ON (like_object.objectTypeID = ' . LikeHandler::getInstance()->getObjectType('de.codequake.cms.likeableNews')->objectTypeID . ' AND like_object.objectID = news.newsID)';
	}

	/**
	 * @inheritDoc
	 */
	public function readObjects() {
		if ($this->objectIDs === null) {
			$this->readObjectIDs();
		}

		parent::readObjects();
		
		$labelNewsIDs = [];
		foreach ($this->objects as $object) {
			if ($object->hasLabels) {
				$labelNewsIDs[] = $object->newsID;
			}
		}
		
		if (!empty($labelNewsIDs)) {
			$assignedLabels = NewsLabelObjectHandler::getInstance()->getAssignedLabels($labelNewsIDs);
			foreach ($assignedLabels as $objectID => $labels) {
				foreach ($labels as $label) {
					$this->objects[$objectID]->addLabel($label);
				}
			}
		}
	}
}
