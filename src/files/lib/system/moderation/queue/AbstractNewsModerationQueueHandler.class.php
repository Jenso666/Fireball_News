<?php

namespace cms\system\moderation\queue;
use cms\data\news\News;
use cms\data\news\NewsAction;
use cms\data\news\NewsList;
use wcf\data\moderation\queue\ModerationQueue;
use wcf\system\moderation\queue\AbstractModerationQueueHandler;
use wcf\system\moderation\queue\ModerationQueueManager;

/**
 * @author      Florian Gail
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
 */
abstract class AbstractNewsModerationQueueHandler extends AbstractModerationQueueHandler {
	/**
	 * @inheritDoc
	 */
	protected $className = 'cms\\data\\news\\News';

	/**
	 * list of news objects
	 * @var	\cms\data\news\News[]
	 */
	protected static $news = array();

	/**
	 * @inheritDoc
	 */
	protected $objectType = 'de.codequake.cms.news';

	/**
	 * @inheritDoc
	 */
	public function assignQueues(array $queues) {
		$newsIDs = array();
		foreach ($queues as $queue) {
			$newsIDs[] = $queue->objectID;
		}

		$news = array();
		$newsList = new NewsList();
		$newsList->getConditionBuilder()->add('news.newsID IN (?)', array($newsIDs));
		$newsList->readObjects();
		/** @var \cms\data\news\News $item */
		foreach ($newsList->getObjects() as $item) {
			$news[$item->newsID] = $item;
		}

		// get assignments
		$orphanedQueueIDs = $assignments = array();
		foreach ($queues as $queue) {
			$assignUser = false;

			if (!isset($news[$queue->objectID])) {
				$orphanedQueueIDs[] = $queue->queueID;
				continue;
			}

			/** @var \cms\data\news\News $newsItem */
			$newsItem = $news[$queue->objectID];
			if ($newsItem->canModerate()) {
				$assignUser = true;
			}

			$assignments[$queue->queueID] = $assignUser;
		}

		ModerationQueueManager::getInstance()->removeOrphans($orphanedQueueIDs);
		ModerationQueueManager::getInstance()->setAssignment($assignments);
	}

	/**
	 * @inheritDoc
	 */
	public function getContainerID($objectID) {
		return 0;
	}

	/**
	 * @inheritDoc
	 */
	public function isValid($objectID) {
		return ($this->getNews($objectID) !== null);
	}

	/**
	 * Returns an news object by news id or null if news id is invalid.
	 *
	 * @param	integer		$objectID
	 * @return	\cms\data\news\News
	 */
	protected function getNews($objectID) {
		if (empty(self::$news[$objectID])) {
			self::$news[$objectID] = new News($objectID);
			if (!self::$news[$objectID]->newsID) {
				self::$news[$objectID] = null;
			}
		}

		return self::$news[$objectID];
	}

	/**
	 * @inheritDoc
	 */
	public function populate(array $queues) {
		$objectIDs = array();
		foreach ($queues as $object) {
			$objectIDs[] = $object->objectID;
		}

		// init news list
		$newsList = new NewsList();
		$newsList->sqlSelects .= 'user_avatar.*, user_table.*';
		$newsList->sqlJoins .= " LEFT JOIN wcf".WCF_N."_user user_table ON (user_table.userID = news.userID)";
		$newsList->sqlJoins .= " LEFT JOIN wcf".WCF_N."_user_avatar user_avatar ON (user_avatar.avatarID = user_table.avatarID)";
		$newsList->getConditionBuilder()->add('news.newsID IN (?)', array($objectIDs));
		$newsList->readObjects();
		$news = $newsList->getObjects();

		// populate
		foreach ($queues as $object) {
			if (isset($news[$object->objectID])) {
				$object->setAffectedObject($news[$object->objectID]);
			}
			else {
				$object->setIsOrphaned();
			}
		}
	}

	/**
	 * @inheritDoc
	 */
	public function removeContent(ModerationQueue $queue, $message) {
		if ($this->isValid($queue->objectID) && !$this->getNews($queue->objectID)->isDeleted) {
			$newsAction = new NewsAction(array($queue->objectID), 'trash', array('reason' => $message));
			$newsAction->executeAction();
		}
	}
}
