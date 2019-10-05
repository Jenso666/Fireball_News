<?php

namespace cms\data\news;

use cms\data\category\NewsCategoryCache;
use cms\system\label\object\NewsLabelObjectHandler;
use cms\system\user\notification\object\NewsUserNotificationObject;
use wcf\data\user\object\watch\UserObjectWatchAction;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IClipboardAction;
use wcf\system\attachment\AttachmentHandler;
use wcf\system\clipboard\ClipboardHandler;
use wcf\system\comment\CommentHandler;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\label\LabelHandler;
use wcf\system\language\LanguageFactory;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\moderation\queue\ModerationQueueActivationManager;
use wcf\system\request\LinkHandler;
use wcf\system\search\SearchIndexManager;
use wcf\system\tagging\TagEngine;
use wcf\system\user\activity\event\UserActivityEventHandler;
use wcf\system\user\activity\point\UserActivityPointHandler;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\user\object\watch\UserObjectWatchHandler;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\visitTracker\VisitTracker;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\StringUtil;
use wcf\util\UserUtil;

/**
 * Executes news-related actions.
 *
 * @author      Jens Krumsieck, Florian Gail
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
 *
 * @method News[] getObjects()
 */
class NewsAction extends AbstractDatabaseObjectAction implements IClipboardAction {
	/**
	 * @inheritDoc
	 */
	protected $className = NewsEditor::class;

	/**
	 * @inheritDoc
	 */
	protected $permissionsDelete = ['mod.fireball.news.canModerateNews'];

	/**
	 * @inheritDoc
	 */
	protected $allowGuestAccess = [
		'getNewsPreview',
		'markAllAsRead',
	];
	
	/**
	 * @var string[]
	 */
	protected $resetCache = ['create', 'delete', 'toggle', 'update', 'enable', 'disable', 'trash', 'restore', 'publish'];

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
			$this->parameters['data']['enableHtml'] = 1;
		}
		
		if (!empty($this->parameters['htmlInputProcessors'])) {
			$this->parameters['data']['enableHtml'] = 1;
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
		
		if (!WCF::getSession()->getPermission('user.fireball.news.canAddNewsWithoutModeration')) {
			$this->parameters['data']['isDisabled'] = 1;
		}

		/** @var News $news */
		$news = parent::create();
		/** @var NewsEditor $newsEditor */
		$newsEditor = new NewsEditor($news);

		if (!empty($this->parameters['data']['authorIDs'])) {
			$sql = "INSERT INTO cms" . WCF_N . "_news_to_user (newsID, userID) VALUES (?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			WCF::getDB()->beginTransaction();
			foreach ($this->parameters['data']['authorIDs'] as $userID) {
				$statement->execute(array($news->newsID, $userID));
			}
			WCF::getDB()->commitTransaction();
		}

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
		if (!empty($this->parameters['htmlInputProcessors'])) {
			foreach ($this->parameters['htmlInputProcessors'] as $inputProcessor) {
				$inputProcessor->setObjectID($news->newsID);
				if (MessageEmbeddedObjectManager::getInstance()->registerObjects($inputProcessor)) {
					$newsEditor->update(['hasEmbeddedObjects' => 1]);
					break;
				}
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

		if (!WCF::getSession()->getPermission('user.fireball.news.canAddNewsWithoutModeration')) {
			ModerationQueueActivationManager::getInstance()->addModeratedContent('de.codequake.cms.news', $news->newsID);
		} else {
			if (!$news->isDelayed && WCF::getSession()->getPermission('user.fireball.news.canAddNewsWithoutModeration')) {
				$publication = new self(array($news), 'publish');
				$publication->executeAction();
			}
		}
		
		// subscribe authors
		if (WCF::getUser()->userID && $news->userID == WCF::getUser()->userID) {
			/** @noinspection PhpUndefinedFieldInspection */
			if (!isset($this->parameters['subscribe'])) {
				$this->parameters['subscribe'] = 1;
			}
			
			if ($this->parameters['subscribe']) {
				$action = new UserObjectWatchAction(array(), 'subscribe', array(
					'data' => array(
						'objectID' => $news->newsID,
						'objectType' => 'de.codequake.cms.news'
					),
					'enableNotification' => UserNotificationHandler::getInstance()->getEventSetting('de.codequake.cms.news.notification', 'update') !== false ? 1 : 0
				));
				$action->executeAction();
			}
		}

		return $news;
	}

	/**
	 * Publishes news.
	 */
	public function publish() {
		/** @var News $newsEditor */
		foreach ($this->objects as $newsEditor) {
			$newsEditor->update([
				'isDelayed' => 0,
				'isDisabled' => 0,
				'isDeleted' => 0,
				'deletedBy' => '',
				'deleteTime' => 0,
				'deletedByID' => null
			]);

			// recent
			if ($newsEditor->userID) {
				UserActivityEventHandler::getInstance()->fireEvent('de.codequake.cms.news.recentActivityEvent', $newsEditor->newsID, $newsEditor->languageID, $newsEditor->userID, $newsEditor->time);
				UserActivityPointHandler::getInstance()->fireEvent('de.codequake.cms.activityPointEvent.news', $newsEditor->newsID, $newsEditor->userID);
			}

			// update search index
			SearchIndexManager::getInstance()->set('de.codequake.cms.news', $newsEditor->newsID, $newsEditor->message, $newsEditor->subject, $newsEditor->time, $newsEditor->userID ?: null, $newsEditor->username, $newsEditor->languageID);
		}
		
		$this->resetUserCache();
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
			$this->parameters['data']['enableHtml'] = 1;
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

			if (!empty($data['authorIDs'])) {
				$sql = "INSERT INTO cms" . WCF_N . "_news_to_user (newsID, userID) VALUES (?, ?)";
				$statement = WCF::getDB()->prepareStatement($sql);
				WCF::getDB()->beginTransaction();
				foreach ($data['authorIDs'] as $userID) {
					$statement->execute(array($news->newsID, $userID));
				}
				WCF::getDB()->commitTransaction();
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
			
			// update watched objects
			if (!$news->isDeleted) {
				UserObjectWatchHandler::getInstance()->updateObject(
					'de.codequake.cms.news',
					$news->newsID,
					'update',
					'de.codequake.cms.news',
					new NewsUserNotificationObject($news->getDecoratedObject())
				);
			}
		}
		
		$this->resetUserCache();
	}
	
	/**
	 * Resets the user storage cache
	 */
	protected function resetUserCache() {
		UserStorageHandler::getInstance()->resetAll('cmsUnreadNews');
		UserStorageHandler::getInstance()->resetAll('cmsUnreadWatchedNews');
		UserStorageHandler::getInstance()->resetAll('cmsWatchedNews');
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
			
			$this->removeActivityEvents($newsIDs);

			// update embedded objects
			MessageEmbeddedObjectManager::getInstance()->removeObjects('de.codequake.cms.news', $newsIDs);
			
			// watch objects
			UserObjectWatchHandler::getInstance()->deleteObjects('de.codequake.cms.news', $newsIDs);
			
			// labels
			LabelHandler::getInstance()->removeLabels(LabelHandler::getInstance()->getObjectType('de.codequake.cms.news')->objectTypeID, $newsIDs);
		}

		if (isset($this->parameters['unmarkItems'])) {
			$this->unmarkItems($newsIDs);
		}
		
		$this->resetUserCache();

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
		
		// mark notifications as read
		if (!empty($this->objects)) {
			UserNotificationHandler::getInstance()->markAsConfirmed('update', 'de.codequake.cms.news.notification', array(WCF::getUser()->userID), $this->objectIDs);
		}
		
		$this->resetUserCache();
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
		
		$this->resetUserCache();
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
	
	/**
	 * Removes moderated content entries for the news with the given ids.
	 *
	 * @param integer[] $newsIDs
	 */
	protected function removeModeratedContent(array $newsIDs) {
		ModerationQueueActivationManager::getInstance()->removeModeratedContent('de.codequake.cms.news', $newsIDs);
	}
	
	/**
	 * Removes user activity events for the news with the given ids.
	 *
	 * @param integer[] $newsIDs
	 */
	protected function removeActivityEvents(array $newsIDs) {
		UserActivityEventHandler::getInstance()->removeEvents('de.codequake.cms.news.recentActivityEvent', $newsIDs);
		UserActivityPointHandler::getInstance()->removeEvents('de.codequake.cms.activityPointEvent.news', $newsIDs);
	}
	
	/**
	 * Validating parameters for enabling.
	 */
	public function validateEnable() {
		$this->readObjects();
		
		if (!WCF::getSession()->getPermission('mod.fireball.news.canModerateNews')) {
			throw new PermissionDeniedException();
		}
		
		foreach ($this->objects as $news) {
			if (!$news->isDisabled || $news->isDeleted) {
				throw new UserInputException('objectIDs');
			}
		}
	}
	
	/**
	 * Enables news
	 */
	public function enable() {
		if (empty($this->objects)) {
			$this->readObjects();
		}
		
		$newsIDs = array();
		foreach ($this->objects as $news) {
			$news->update(array('isDisabled' => 0));
			
			$newsIDs[] = $news->newsID;
		}
		
		$newsAction = new self($this->objects, 'publish');
		$newsAction->executeAction();
		
		$this->removeModeratedContent($newsIDs);
		
		$this->unmarkItems($newsIDs);
	}
	
	/**
	 * Validating parameters for enabling.
	 */
	public function validateDisable() {
		$this->readObjects();
		
		if (!WCF::getSession()->getPermission('mod.fireball.news.canModerateNews')) {
			throw new PermissionDeniedException();
		}
		
		foreach ($this->objects as $news) {
			if ($news->isDisabled || $news->isDeleted) {
				throw new UserInputException('objectIDs');
			}
		}
	}
	
	/**
	 * Disables news
	 */
	public function disable() {
		if (empty($this->objects)) {
			$this->readObjects();
		}
		
		$newsIDs = array();
		foreach ($this->objects as $news) {
			$news->update(array('isDisabled' => 1));
			
			$newsIDs[] = $news->newsID;
			ModerationQueueActivationManager::getInstance()->addModeratedContent('de.codequake.cms.news', $news->newsID);
		}
		
		// delete notifications
		UserNotificationHandler::getInstance()->removeNotifications('de.codequake.cms.news.notification', $newsIDs);
		
		$this->removeActivityEvents($newsIDs);
		
		$this->unmarkItems($newsIDs);
	}
	
	/**
	 * Validating parameters for trashing news.
	 */
	public function validateTrash() {
		$this->readObjects();
		
		if (!WCF::getSession()->getPermission('mod.fireball.news.canModerateNews')) {
			throw new PermissionDeniedException();
		}
		
		foreach ($this->objects as $news) {
			if ($news->isDeleted) {
				throw new UserInputException('objectIDs');
			}
		}
	}
	
	/**
	 * Trashes given news.
	 */
	public function trash() {
		if (empty($this->objects)) {
			$this->readObjects();
		}
		
		$deleteReason = (isset($this->parameters['data']['reason']) ? StringUtil::trim($this->parameters['data']['reason']) : '');
		
		$newsIDs = array();
		foreach ($this->objects as $news) {
			$news->update(array(
				'isDeleted' => 1,
				'deleteTime' => TIME_NOW,
				'deletedByID' => WCF::getUser()->userID,
				'deletedBy' => WCF::getUser()->username,
				'deleteReason' => $deleteReason
			));
			
			$newsIDs[] = $news->newsID;
		}
		
		$this->unmarkItems($newsIDs);
	}
	
	/**
	 * Validating parameters for restoring news.
	 */
	public function validateRestore() {
		$this->readObjects();
		
		if (!WCF::getSession()->getPermission('mod.fireball.news.canModerateNews')) {
			throw new PermissionDeniedException();
		}
		
		foreach ($this->objects as $news) {
			if (!$news->isDeleted) {
				throw new UserInputException('objectIDs');
			}
		}
	}
	
	/**
	 * Restores given news.
	 */
	public function restore() {
		if (empty($this->objects)) {
			$this->readObjects();
		}
		
		$newsIDs = array();
		foreach ($this->objects as $news) {
			$news->update(array(
				'isDeleted' => 0,
				'deleteTime' => 0,
				'deletedByID' => null,
				'deletedBy' => '',
				'deleteReason' => 0
			));
			
			$newsIDs[] = $news->newsID;
		}
		
		$this->unmarkItems($newsIDs);
	}
	
	/**
	 * Validates parameters to assign labels.
	 */
	public function validateAssignLabel() {
		$this->readInteger('categoryID');
		
		$category = NewsCategoryCache::getInstance()->getCategory($this->parameters['categoryID']);
		if ($category === null) {
			throw new UserInputException('categoryID');
		}
		
		$this->readObjects();
		if (empty($this->objects)) {
			throw new UserInputException('objectIDs');
		}
		
		foreach ($this->getObjects() as $news) {
			if (!$news->canModerate()) {
				throw new PermissionDeniedException();
			}
		}
		
		$this->parameters['labelIDs'] = empty($this->parameters['labelIDs']) ? array() : ArrayUtil::toIntegerArray($this->parameters['labelIDs']);
		if (!empty($this->parameters['labelIDs'])) {
			$labelIDs = NewsCategoryCache::getInstance()->getLabelGroupIDs($category->categoryID);
			if (empty($labelIDs)) {
				throw new PermissionDeniedException();
			}
			
			$labelGroups = LabelHandler::getInstance()->getLabelGroups($labelIDs);
			foreach ($this->parameters['labelIDs'] as $groupID => $labelID) {
				if (!isset($labelGroups[$groupID]) || !$labelGroups[$groupID]->isValid($labelID)) {
					throw new UserInputException('labelIDs');
				}
			}
		}
	}
	
	/**
	 * Assigns labels and returns the updated list.
	 *
	 * @return	mixed[][]
	 */
	public function assignLabel() {
		$objectTypeID = LabelHandler::getInstance()->getObjectType('de.codequake.cms.news')->objectTypeID;
		
		$category = NewsCategoryCache::getInstance()->getCategory($this->parameters['categoryID']);
		
		$newsIDs = array();
		foreach ($this->getObjects() as $news) {
			$newsIDs[] = $news->newsID;
		}
		
		// fetch old labels for modification log creation
		$oldLabels = LabelHandler::getInstance()->getAssignedLabels($objectTypeID, $newsIDs);
		
		foreach ($this->getObjects() as $news) {
			LabelHandler::getInstance()->setLabels($this->parameters['labelIDs'], $objectTypeID, $news->newsID);
			
			// update hasLabels flag
			$news->update(array(
				'hasLabels' => !empty($this->parameters['labelIDs']) ? 1 : 0
			));
		}
		
		$assignedLabels = LabelHandler::getInstance()->getAssignedLabels($objectTypeID, $newsIDs);
		/** @var Label[] $labelList */
		$labelList = null;
		if (!empty($assignedLabels)) {
			// get labels from first object
			$labelList = reset($assignedLabels);
		}
		
		$labels = array();
		if ($labelList !== null) {
			$tmp = array();
			
			/** @var \wcf\data\label\Label $label */
			foreach ($labelList as $label) {
				$tmp[$label->labelID] = array(
					'cssClassName' => $label->cssClassName,
					'label' => $label->getTitle(),
					'link' => LinkHandler::getInstance()->getLink('NewsList', array(
						'application' => 'cms',
						'object' => $category
					), 'labelIDs['.$label->groupID.']='.$label->labelID)
				);
			}
			
			$labelGroups = NewsLabelObjectHandler::getInstance()->getLabelGroups();
			foreach ($labelGroups as $labelGroup) {
				foreach ($tmp as $labelID => $labelData) {
					if ($labelGroup->isValid($labelID)) {
						$labels[] = $labelData;
						break;
					}
				}
			}
		}
		
		$this->unmarkItems($newsIDs);
		
		return array(
			'labels' => $labels
		);
	}
}
