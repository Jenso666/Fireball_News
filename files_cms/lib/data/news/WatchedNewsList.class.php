<?php

namespace cms\data\news;

use cms\data\category\NewsCategory;
use wcf\system\user\object\watch\UserObjectWatchHandler;
use wcf\system\WCF;

/**
 * @author      Florian Gail
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
 */
class WatchedNewsList extends ViewableNewsList {
	/**
	 * Creates a new WatchedNewsList object.
	 */
	public function __construct() {
		parent::__construct();
		
		$categoryIDs = NewsCategory::getAccessibleCategoryIDs();
		if (empty($categoryIDs)) {
			$this->getConditionBuilder()->add('1=0');
		}
		else {
			$objectTypeID = UserObjectWatchHandler::getInstance()->getObjectTypeID('de.codequake.cms.news');
			
			$this->sqlConditionJoins = "LEFT JOIN cms" . WCF_N . "_news news ON (news.newsID = user_object_watch.objectID)";
			$this->getConditionBuilder()->add('user_object_watch.objectTypeID = ?', array($objectTypeID));
			$this->getConditionBuilder()->add('user_object_watch.userID = ?', array(WCF::getUser()->userID));
			$this->getConditionBuilder()->add('news.newsID IN (?)', array($categoryIDs));
			$this->getConditionBuilder()->add('news.isDeleted = 0 AND news.isDisabled = 0');
		}
	}
	
	/** @noinspection PhpMissingParentCallCommonInspection */
	/**
	 * @inheritDoc
	 */
	public function countObjects() {
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf" . WCF_N . "_user_object_watch user_object_watch
			" . $this->sqlConditionJoins . "
			" . $this->getConditionBuilder()->__toString();
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($this->getConditionBuilder()->getParameters());
		$row = $statement->fetchArray();
		
		return $row['count'];
	}
	
	/** @noinspection PhpMissingParentCallCommonInspection */
	/**
	 * @inheritDoc
	 */
	public function readObjectIDs() {
		$sql = "SELECT	user_object_watch.objectID AS objectID
			FROM	wcf" . WCF_N . "_user_object_watch user_object_watch
				" . $this->sqlConditionJoins . "
				" . $this->getConditionBuilder()->__toString() . "
				" . (!empty($this->sqlOrderBy) ? "ORDER BY " . $this->sqlOrderBy : '');
		$statement = WCF::getDB()->prepareStatement($sql, $this->sqlLimit, $this->sqlOffset);
		$statement->execute($this->getConditionBuilder()->getParameters());
		$this->objectIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);
	}
}
