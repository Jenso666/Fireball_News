<?php

namespace cms\system\worker;
use cms\data\news\NewsEditor;
use cms\data\news\NewsList;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\user\activity\point\UserActivityPointHandler;
use wcf\system\worker\AbstractRebuildDataWorker;
use wcf\system\WCF;

/**
 * Implements the news rebuild data worker
 *
 * @author		Florian Gail
 * @copyright	2014-2016 Florian Gail <https://www.mysterycode.de/>
 * @license	Kostenlose Plugins <https://downloads.mysterycode.de/license/6-kostenlose-plugins/>
 * @package     de.codequake.cms.news
 */
class NewsRebuildDataWorker extends AbstractRebuildDataWorker {
	/**
	 * @inheritDoc
	 */
	protected $objectListClassName = NewsList::class;
	
	/**
	 * @inheritDoc
	 */
	protected $limit = 400;

	/**
	 * @var HtmlInputProcessor
	 */
	protected $htmlInputProcessor;

	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->sqlOrderBy = 'news.newsID';
	}

	/**
	 * @inheritDoc
	 */
	public function execute() {
		$this->objectList->getConditionBuilder()->add('news.newsID BETWEEN ? AND ?', [$this->limit * $this->loopCount + 1, $this->limit * $this->loopCount + $this->limit]);

		parent::execute();
		
		if (!$this->loopCount) {
			// reset activity points
			UserActivityPointHandler::getInstance()->reset('de.codequake.cms.activityPointEvent.news');
		}
		
		if (!count($this->objectList)) {
			return;
		}
		
		// fetch cumulative likes
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("objectTypeID = ?", [
			ObjectTypeCache::getInstance()->getObjectTypeIDByName('com.woltlab.wcf.like.likeableObject', 'de.codequake.cms.likeableNews')
		]);
		$conditions->add("objectID IN (?)", [
			$this->objectList->getObjectIDs()
		]);

		// prepare statements
		$attachmentObjectType = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.attachment.objectType', 'de.codequake.cms.news');
		$sql = "SELECT		COUNT(*) AS attachments
			FROM		wcf".WCF_N."_attachment
			WHERE		objectTypeID = ?
					AND objectID = ?";
		$attachmentStatement = WCF::getDB()->prepareStatement($sql);
		
		$sql = "SELECT	objectID, cumulativeLikes
			FROM	wcf" . WCF_N . "_like_object
			" . $conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		$cumulativeLikes = [];
		while ($row = $statement->fetchArray()) {
			$cumulativeLikes[$row['objectID']] = $row['cumulativeLikes'];
		}
		
		$userStats = [];
		WCF::getDB()->beginTransaction();
		/** @var \cms\data\news\News $news */
		foreach ($this->objectList as $news) {
			// editor
			$editor = new NewsEditor($news);

			// update activity points
			if ($news->userID) {
				if (!isset($userStats[$news->userID])) {
					$userStats[$news->userID] = 0;
				}
				$userStats[$news->userID]++;
			}

			// count attachments
			$attachmentStatement->execute([$attachmentObjectType->objectTypeID, $news->newsID]);
			$row = $attachmentStatement->fetchSingleRow();
			$data['attachments'] = $row['attachments'];

			// update cumulative likes
			$data['cumulativeLikes'] = isset($cumulativeLikes[$news->newsID]) ? $cumulativeLikes[$news->newsID] : 0;

			// update description
			if (!$news->enableHtml) {
				$this->getHtmlInputProcessor()->process($news->message, 'de.codequake.cms.news', $news->newsID, true);
				$data['message'] = $this->getHtmlInputProcessor()->getHtml();
				$data['enableHtml'] = 1;
			} else {
				$this->getHtmlInputProcessor()->processEmbeddedContent($news->message, 'de.codequake.cms.news', $news->newsID);
			}
			if (MessageEmbeddedObjectManager::getInstance()->registerObjects($this->getHtmlInputProcessor())) {
				$data['hasEmbeddedObjects'] = 1;
			} else {
				$data['hasEmbeddedObjects'] = 0;
			}

			// update data
			$editor->update($data);
		}
		WCF::getDB()->commitTransaction();
		
		// update activity points
		UserActivityPointHandler::getInstance()->fireEvents('de.codequake.cms.activityPointEvent.news', $userStats, false);
	}

	/**
	 * @return HtmlInputProcessor
	 */
	protected function getHtmlInputProcessor() {
		if ($this->htmlInputProcessor === null) {
			$this->htmlInputProcessor = new HtmlInputProcessor();
		}

		return $this->htmlInputProcessor;
	}
}
