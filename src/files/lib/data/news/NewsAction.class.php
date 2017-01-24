<?php

/**
 * @author    Jens Krumsieck
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\data\news;

use cms\data\category\NewsCategory;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IClipboardAction;
use wcf\system\attachment\AttachmentHandler;
use wcf\system\clipboard\ClipboardHandler;
use wcf\system\comment\CommentHandler;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\language\LanguageFactory;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\search\SearchIndexManager;
use wcf\system\tagging\TagEngine;
use wcf\system\user\activity\event\UserActivityEventHandler;
use wcf\system\user\activity\point\UserActivityPointHandler;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\visitTracker\VisitTracker;
use wcf\system\WCF;
use wcf\util\UserUtil;

/**
 * Executes news-related actions.
 */
class NewsAction extends AbstractDatabaseObjectAction implements IClipboardAction {
	/**
	 * @inheritDoc
	 */
	protected $className = NewsEditor::class;

	/**
	 * @inheritDoc
	 */
	protected $permissionsDelete = ['mod.fireball.news.canModerateNews',];

	/**
	 * @inheritDoc
	 */
	protected $allowGuestAccess = [
		'getNewsPreview',
		'markAllAsRead',
	];

	/**
	 * @var News
	 */
	public $news;

	/**
	 * @inheritDoc
	 */
	public function create() {
		if (!empty($this->parameters['htmlInputProcessor'])) {
			/** @noinspection PhpUndefinedMethodInspection */
			$this->parameters['data']['message'] = $this->parameters['htmlInputProcessor']->getHtml();
		}

		// count attachments
		if (isset($this->parameters['attachmentHandler']) && $this->parameters['attachmentHandler'] !== null) {
			$this->parameters['data']['attachments'] = count($this->parameters['attachmentHandler']);
		}

		if (LOG_IP_ADDRESS) {
			// add ip address
			if (!isset($this->parameters['data']['ipAddress'])) {
				$this->parameters['data']['ipAddress'] = WCF::getSession()->ipAddress;
			}
		}
		else {
			// do not track ip address
			if (isset($this->parameters['data']['ipAddress'])) {
				unset($this->parameters['data']['ipAddress']);
			}
		}

		/** @var News $news */
		$news = parent::create();
		/** @var NewsEditor $newsEditor */
		$newsEditor = new NewsEditor($news);

		// update attachments
		if (isset($this->parameters['attachmentHandler']) && $this->parameters['attachmentHandler'] !== null) {
			$this->parameters['attachmentHandler']->updateObjectID($news->newsID);
		}

		// save embedded objects
		if (!empty($this->parameters['htmlInputProcessor'])) {
			/** @noinspection PhpUndefinedMethodInspection */
			$this->parameters['htmlInputProcessor']->setObjectID($news->newsID);
			if (MessageEmbeddedObjectManager::getInstance()->registerObjects($this->parameters['htmlInputProcessor'])) {
				$newsEditor->update(['hasEmbeddedObjects' => 1]);
			}
		}

		// handle categories
		$newsEditor->updateCategoryIDs($this->parameters['categoryIDs']);
		$newsEditor->setCategoryIDs($this->parameters['categoryIDs']);

		// langID != 0
		$languageID = (!isset($this->parameters['data']['languageID']) || ($this->parameters['data']['languageID'] === null)) ? LanguageFactory::getInstance()->getDefaultLanguageID() : $this->parameters['data']['languageID'];

		// tags
		if (!empty($this->parameters['tags'])) {
			TagEngine::getInstance()->addObjectTags('de.codequake.cms.news', $news->newsID, $this->parameters['tags'], $languageID);
		}

		if (!$news->isDisabled) {
			$publishAction = new self([$news], 'publish');
			$publishAction->executeAction();
		}

		return $news;
	}

	/**
	 * Publishes news.
	 */
	public function publish() {
		/** @var News $newsEditor */
		foreach ($this->objects as $newsEditor) {
			$newsEditor->update(['isDisabled' => 0]);

			// recent
			if ($newsEditor->userID) {
				UserActivityEventHandler::getInstance()->fireEvent('de.codequake.cms.news.recentActivityEvent', $newsEditor->newsID, $newsEditor->languageID, $newsEditor->userID, $newsEditor->time);
				UserActivityPointHandler::getInstance()->fireEvent('de.codequake.cms.activityPointEvent.news', $newsEditor->newsID, $newsEditor->userID);
			}

			// update search index
			SearchIndexManager::getInstance()->set('de.codequake.cms.news', $newsEditor->newsID, $newsEditor->message, $newsEditor->subject, $newsEditor->time, $newsEditor->userID ?: null, $newsEditor->username, $newsEditor->languageID);
		}

		// reset storage
		UserStorageHandler::getInstance()->resetAll('cmsUnreadNews');
	}

	/**
	 * @inheritDoc
	 */
	public function update() {
		// count attachments
		if (isset($this->parameters['attachmentHandler']) && $this->parameters['attachmentHandler'] !== null) {
			$this->parameters['data']['attachments'] = count($this->parameters['attachmentHandler']);
		}

		if (!empty($this->parameters['htmlInputProcessor'])) {
			/** @noinspection PhpUndefinedMethodInspection */
			$this->parameters['data']['message'] = $this->parameters['htmlInputProcessor']->getHtml();
		}

		parent::update();

		// update embedded objects
		if (!empty($this->parameters['htmlInputProcessor'])) {
			/** @var News $object */
			foreach ($this->getObjects() as $object) {
				$this->parameters['htmlInputProcessor']->setObjectID($object->newsID);
				if ($object->hasEmbeddedObjects != MessageEmbeddedObjectManager::getInstance()->registerObjects($this->parameters['htmlInputProcessor'])) {
					$object->update(['hasEmbeddedObjects' => $object->hasEmbeddedObjects ? 0 : 1]);
				}
			}
		}

		$objectIDs = [];
		/** @var News $news */
		foreach ($this->objects as $news) {
			$objectIDs[] = $news->newsID;
		}

		if (!empty($objectIDs)) {
			SearchIndexManager::getInstance()->delete('de.codequake.cms.news', $objectIDs);
		}

		/** @var News $news */
		foreach ($this->objects as $news) {
			if (isset($this->parameters['categoryIDs'])) {
				$news->updateCategoryIDs($this->parameters['categoryIDs']);
			}

			// update tags
			$tags = [];
			if (isset($this->parameters['tags'])) {
				$tags = $this->parameters['tags'];
				unset($this->parameters['tags']);
			}
			if (!empty($tags)) {
				$languageID = (!isset($this->parameters['data']['languageID']) || ($this->parameters['data']['languageID'] === null)) ? LanguageFactory::getInstance()->getDefaultLanguageID() : $this->parameters['data']['languageID'];
				TagEngine::getInstance()->addObjectTags('de.codequake.cms.news', $news->newsID, $tags, $languageID);
			}

			// update search index
			SearchIndexManager::getInstance()->set('de.codequake.cms.news', $news->newsID, $news->message, $news->subject, $news->time, $news->userID, $news->username, $news->languageID);
		}
	}

	/**
	 * @inheritDoc
	 */
	public function delete() {
		$attachmentIDs = $commentIDs = $newsIDs = [];
		foreach ($this->objects as $news) {
			/** @var News $news */
			if ($news->attachments != 0) {
				$attachmentIDs[] = $news->newsID;
			}
			if ($news->comments != 0) {
				$commentIDs[] = $news->newsID;
			}

			if (!$news->isDeleted) {
				$newsIDs[] = $news->newsID;
			}
		}

		// remove attachments
		if (!empty($attachedNewsIDs)) {
			AttachmentHandler::removeAttachments('de.codequake.cms.news', $attachedNewsIDs);
		}

		// remove comments
		if (!empty($commentIDs)) {
			CommentHandler::getInstance()->deleteObjects('de.codequake.cms.news.comment', $commentIDs);
		}

		if (!empty($newsIDs)) {
			// delete old search index entries
			SearchIndexManager::getInstance()->delete('de.codequake.cms.news', $newsIDs);

			// remove activity points
			UserActivityPointHandler::getInstance()->removeEvents('de.codequake.cms.activityPointEvent.news', $newsIDs);

			// update embedded objects
			MessageEmbeddedObjectManager::getInstance()->removeObjects('de.codequake.cms.news', $newsIDs);
		}

		if (isset($this->parameters['unmarkItems'])) {
			$this->unmarkItems($newsIDs);
		}

		return parent::delete();
	}

	/**
	 * Validates parameters to mark news as read.
	 */
	public function validateMarkAsRead() {
		if (empty($this->objects)) {
			$this->readObjects();

			if (empty($this->objects)) {
				throw new UserInputException('objectIDs');
			}
		}
	}

	/**
	 * Mark news as read.
	 */
	public function markAsRead() {
		if (empty($this->parameters['visitTime'])) {
			$this->parameters['visitTime'] = TIME_NOW;
		}

		if (empty($this->objects)) {
			$this->readObjects();
		}

		/** @var News $news */
		foreach ($this->objects as $news) {
			VisitTracker::getInstance()->trackObjectVisit('de.codequake.cms.news', $news->newsID, $this->parameters['visitTime']);
		}

		// reset storage
		if (WCF::getUser()->userID) {
			UserStorageHandler::getInstance()->reset([WCF::getUser()->userID], 'cmsUnreadNews');
		}
	}

	/**
	 * Validates parameters to mark all news as read.
	 */
	public function validateMarkAllAsRead() {
	}

	/**
	 * Marks all news as read.
	 */
	public function markAllAsRead() {
		VisitTracker::getInstance()->trackTypeVisit('de.codequake.cms.news');

		// reset storage
		if (WCF::getUser()->userID) {
			UserStorageHandler::getInstance()->reset([WCF::getUser()->userID], 'cmsUnreadNews');
		}
	}

	/**
	 * Validates parameters and permissions to get the ip log for a news.
	 *
	 * @throws \wcf\system\exception\PermissionDeniedException
	 * @throws \wcf\system\exception\UserInputException
	 */
	public function validateGetIpLog() {
		if (!LOG_IP_ADDRESS) {
			throw new PermissionDeniedException();
		}

		if (isset($this->parameters['newsID'])) {
			$this->news = new News($this->parameters['newsID']);
		}
		if ($this->news === null || !$this->news->newsID) {
			throw new UserInputException('newsID');
		}

		if (!$this->news->canRead()) {
			throw new PermissionDeniedException();
		}
	}

	/**
	 * Returns the ip log for a news.
	 *
	 * @return array
	 * @throws \wcf\system\database\DatabaseException
	 * @throws \wcf\system\database\exception\DatabaseQueryException
	 * @throws \wcf\system\database\exception\DatabaseQueryExecutionException
	 */
	public function getIpLog() {
		// get ip addresses of the author
		$authorIpAddresses = News::getIpAddressByAuthor($this->news->userID, $this->news->username, $this->news->ipAddress);

		// resolve hostnames
		$newIpAddresses = [];
		foreach ($authorIpAddresses as $ipAddress) {
			$ipAddress = UserUtil::convertIPv6To4($ipAddress);

			$newIpAddresses[] = [
				'hostname' => @gethostbyaddr($ipAddress),
				'ipAddress' => $ipAddress,
			];
		}
		$authorIpAddresses = $newIpAddresses;

		// get other users of this ip address
		$otherUsers = [];
		if ($this->news->ipAddress) {
			$otherUsers = News::getAuthorByIpAddress($this->news->ipAddress, $this->news->userID, $this->news->username);
		}

		$ipAddress = UserUtil::convertIPv6To4($this->news->ipAddress);

		if ($this->news->userID) {
			$sql = 'SELECT registrationIpAddress
                FROM wcf' . WCF_N . '_user
                WHERE userID = ?';
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$this->news->userID]);
			$row = $statement->fetchArray();

			if ($row !== false && $row['registrationIpAddress']) {
				$registrationIpAddress = UserUtil::convertIPv6To4($row['registrationIpAddress']);
				WCF::getTPL()->assign([
					'registrationIpAddress' => [
						'hostname' => @gethostbyaddr($registrationIpAddress),
						'ipAddress' => $registrationIpAddress,
					],
				]);
			}
		}

		WCF::getTPL()->assign([
			'authorIpAddresses' => $authorIpAddresses,
			'ipAddress' => [
				'hostname' => @gethostbyaddr($ipAddress),
				'ipAddress' => $ipAddress,
			],
			'otherUsers' => $otherUsers,
			'news' => $this->news,
		]);

		return [
			'newsID' => $this->news->newsID,
			'template' => WCF::getTPL()->fetch('newsIpAddress', 'cms'),
		];
	}

	/**
	 * Validates parameters and permissions to get a news preview.
	 *
	 * @throws \wcf\system\exception\UserInputException
	 */
	public function validateGetNewsPreview() {
		/** @var NewsEditor|News $news */
		$news = $this->getSingleObject();

		if (!$news->canRead()) {
			throw new PermissionDeniedException();
		}
	}

	/**
	 * Return the preview of a news.
	 *
	 * @return array
	 */
	public function getNewsPreview() {
		/** @var NewsEditor|News $news */
		$news = $this->getSingleObject();

		// why did i use viewable list, when having a news object ???
		WCF::getTPL()->assign(['news' => new ViewableNews(new News($news->newsID))]);

		return ['template' => WCF::getTPL()->fetch('newsPreview', 'cms')];
	}

	/**
	 * @inheritDoc
	 */
	public function validateUnmarkAll() {
		// does nothing
	}

	/**
	 * @inheritDoc
	 */
	public function unmarkAll() {
		ClipboardHandler::getInstance()->removeItems(ClipboardHandler::getInstance()->getObjectTypeID('de.codequake.cms.news'));
	}

	/**
	 * @param array $objectIDs
	 *
	 * @throws \wcf\system\exception\SystemException
	 */
	protected function unmarkItems(array $objectIDs = []) {
		if (empty($objectIDs)) {
			/** @var News $news */
			foreach ($this->objects as $news) {
				$objectIDs[] = $news->newsID;
			}
		}

		if (0 !== count($objectIDs)) {
			ClipboardHandler::getInstance()->unmark($objectIDs, ClipboardHandler::getInstance()->getObjectTypeID('de.codequake.cms.news'));
		}
	}
}
