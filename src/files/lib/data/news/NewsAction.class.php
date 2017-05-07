<?php

/**
 * @author    Jens Krumsieck
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\data\news;

use cms\system\user\notification\object\NewsUserNotificationObject;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IClipboardAction;
use wcf\data\user\object\watch\UserObjectWatchAction;
use wcf\system\attachment\AttachmentHandler;
use wcf\system\clipboard\ClipboardHandler;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\language\LanguageFactory;
use wcf\system\search\SearchIndexManager;
use wcf\system\tagging\TagEngine;
use wcf\system\user\activity\event\UserActivityEventHandler;
use wcf\system\user\activity\point\UserActivityPointHandler;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\user\object\watch\UserObjectWatchHandler;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\visitTracker\VisitTracker;
use wcf\system\WCF;
use wcf\util\UserUtil;

/**
 * Executes news-related actions.
 */
class NewsAction extends AbstractDatabaseObjectAction implements IClipboardAction {
	/**
	 * {@inheritdoc}
	 */
	protected $className = 'cms\data\news\NewsEditor';

	/**
	 * {@inheritdoc}
	 */
	protected $permissionsDelete = array('mod.fireball.news.canModerateNews',);

	/**
	 * {@inheritdoc}
	 */
	protected $allowGuestAccess = array(
		'getNewsPreview',
		'markAllAsRead',
	);

	public $news;

	/**
	 * {@inheritdoc}
	 */
	public function create() {
		$data = $this->parameters['data'];

		// count attachments
		if (isset($this->parameters['attachmentHandler']) && $this->parameters['attachmentHandler'] !== null) {
			$data['attachments'] = count($this->parameters['attachmentHandler']);
		}

		if (LOG_IP_ADDRESS) {
			// add ip address
			if (!isset($data['ipAddress'])) {
				$data['ipAddress'] = WCF::getSession()->ipAddress;
			}
		}
		else {
			// do not track ip address
			if (isset($data['ipAddress'])) {
				unset($data['ipAddress']);
			}
		}

		$news = call_user_func(array(
			$this->className,
			'create'
		), $data);
		$newsEditor = new NewsEditor($news);

		if (!empty($data['authorIDs'])) {
			$sql = "INSERT INTO cms" . WCF_N . "_news_to_user (newsID, userID) VALUES (?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			WCF::getDB()->beginTransaction();
			foreach ($data['authorIDs'] as $userID) {
				$statement->execute(array($news->newsID, $userID));
			}
			WCF::getDB()->commitTransaction();
		}

		// update attachments
		if (isset($this->parameters['attachmentHandler']) && $this->parameters['attachmentHandler'] !== null) {
			$this->parameters['attachmentHandler']->updateObjectID($news->newsID);
		}

		// handle categories
		$newsEditor->updateCategoryIDs($this->parameters['categoryIDs']);
		$newsEditor->setCategoryIDs($this->parameters['categoryIDs']);

		// langID != 0
		$languageID = (!isset($this->parameters['data']['languageID']) || ($this->parameters['data']['languageID'] === null)) ? LanguageFactory::getInstance()->getDefaultLanguageID() : $this->parameters['data']['languageID'];

		// tags
		if (!empty($this->parameters['tags'])) {
			TagEngine::getInstance()->addObjectTags('de.codequake.cms.news', $news->newsID, $this->parameters['tags'],
				$languageID);
		}

		if (!$news->isDisabled) {
			// recent
			if ($news->userID !== null && $news->userID != 0) {
				UserActivityEventHandler::getInstance()->fireEvent('de.codequake.cms.news.recentActivityEvent',
					$news->newsID, $languageID, $news->userID, $news->time);
				UserActivityPointHandler::getInstance()->fireEvent('de.codequake.cms.activityPointEvent.news',
					$news->newsID, $news->userID);
			}

			// update search index
			SearchIndexManager::getInstance()->add('de.codequake.cms.news', $news->newsID, $news->message,
				$news->subject, $news->time, $news->userID, $news->username, $languageID);

			// reset storage
			UserStorageHandler::getInstance()->resetAll('cmsUnreadNews');
		}
		
		// subscribe authors
		if (WCF::getUser()->userID && $news->userID == WCF::getUser()->userID) {
			/** @noinspection PhpUndefinedFieldInspection */
			if (!isset($this->parameters['subscribe']) && WCF::getUser()->watchThreadOnReply) {
				$this->parameters['subscribe'] = 1;
			}
			
			if ($this->parameters['subscribe']) {
				$action = new UserObjectWatchAction([], 'subscribe', [
					'data' => [
						'objectID' => $news->newsID,
						'objectType' => 'de.codequake.cms.news'
					],
					'enableNotification' => UserNotificationHandler::getInstance()->getEventSetting('de.codequake.cms.news', 'update') !== false ? 1 : 0
				]);
				$action->executeAction();
			}
		}

		return $news;
	}

	/**
	 * Publishes news.
	 */
	public function publish() {
		foreach ($this->objects as $news) {
			$news->update(array('isDisabled' => 0));

			// recent
			UserActivityEventHandler::getInstance()->fireEvent('de.codequake.cms.news.recentActivityEvent',
				$news->newsID, $news->languageID, $news->userID, $news->time);
			UserActivityPointHandler::getInstance()->fireEvent('de.codequake.cms.activityPointEvent.news',
				$news->newsID, $news->userID);

			// update search index
			SearchIndexManager::getInstance()->add('de.codequake.cms.news', $news->newsID, $news->message,
				$news->subject, $news->time, $news->userID, $news->username, $news->languageID);
		}
		
		$this->resetUserCache();

		// reset storage
		UserStorageHandler::getInstance()->resetAll('cmsUnreadNews');
	}

	/**
	 * {@inheritdoc}
	 */
	public function update() {
		// count attachments
		if (isset($this->parameters['attachmentHandler']) && $this->parameters['attachmentHandler'] !== null) {
			$this->parameters['data']['attachments'] = count($this->parameters['attachmentHandler']);
		}

		parent::update();

		$objectIDs = array();
		foreach ($this->objects as $news) {
			$objectIDs[] = $news->newsID;
		}

		if (0 !== count($objectIDs)) {
			SearchIndexManager::getInstance()->delete('de.codequake.cms.news', $objectIDs);
		}

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
			$tags = array();
			if (isset($this->parameters['tags'])) {
				$tags = $this->parameters['tags'];
				unset($this->parameters['tags']);
			}
			if (!empty($tags)) {
				$languageID = (!isset($this->parameters['data']['languageID']) || ($this->parameters['data']['languageID'] === null)) ? LanguageFactory::getInstance()->getDefaultLanguageID() : $this->parameters['data']['languageID'];
				TagEngine::getInstance()->addObjectTags('de.codequake.cms.news', $news->newsID, $tags, $languageID);
			}

			// update search index
			SearchIndexManager::getInstance()->add('de.codequake.cms.news', $news->newsID, $news->message,
				$news->subject, $news->time, $news->userID, $news->username, $news->languageID);
			
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
		UserStorageHandler::getInstance()->resetAll('cmsUnreadWatchedNews');
		UserStorageHandler::getInstance()->resetAll('cmsWatchedNews');
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete() {
		$newsIDs = array();
		$attachedNewsIDs = array();
		foreach ($this->objects as $news) {
			$newsIDs[] = $news->newsID;
			if ($news->attachments != 0) {
				$attachedNewsIDs[] = $news->newsID;
			}
		}

		// remove activity points
		UserActivityPointHandler::getInstance()->removeEvents('de.codequake.cms.activityPointEvent.news', $newsIDs);

		// remove attaches
		if (0 !== count($attachedNewsIDs)) {
			AttachmentHandler::removeAttachments('de.codequake.cms.news', $attachedNewsIDs);
		}

		// delete old search index entries
		if (0 !== count($newsIDs)) {
			SearchIndexManager::getInstance()->delete('de.codequake.cms.news', $newsIDs);
			UserObjectWatchHandler::getInstance()->deleteObjects('de.codequake.cms.news', $newsIDs);
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
		if (0 === count($this->objects)) {
			$this->readObjects();

			if (0 === count($this->objects)) {
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

		if (0 === count($this->objects)) {
			$this->readObjects();
		}

		foreach ($this->objects as $news) {
			VisitTracker::getInstance()->trackObjectVisit('de.codequake.cms.news', $news->newsID, $this->parameters['visitTime']);
		}

		// reset storage
		if (WCF::getUser()->userID) {
			UserStorageHandler::getInstance()->reset(array(WCF::getUser()->userID), 'cmsUnreadNews');
		}
		
		// mark notifications as read
		if (!empty($this->objects)) {
			UserNotificationHandler::getInstance()->markAsConfirmed('update', 'de.codequake.cms.news', [WCF::getUser()->userID], $this->objectIDs);
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
			UserStorageHandler::getInstance()->reset(array(WCF::getUser()->userID), 'cmsUnreadNews');
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
	 */
	public function getIpLog() {
		// get ip addresses of the author
		$authorIpAddresses = News::getIpAddressByAuthor($this->news->userID, $this->news->username, $this->news->ipAddress);

		// resolve hostnames
		$newIpAddresses = array();
		foreach ($authorIpAddresses as $ipAddress) {
			$ipAddress = UserUtil::convertIPv6To4($ipAddress);

			$newIpAddresses[] = array(
				'hostname' => @gethostbyaddr($ipAddress),
				'ipAddress' => $ipAddress,
			);
		}
		$authorIpAddresses = $newIpAddresses;

		// get other users of this ip address
		$otherUsers = array();
		if ($this->news->ipAddress) {
			$otherUsers = News::getAuthorByIpAddress($this->news->ipAddress, $this->news->userID, $this->news->username);
		}

		$ipAddress = UserUtil::convertIPv6To4($this->news->ipAddress);

		if ($this->news->userID) {
			$sql = 'SELECT registrationIpAddress
				FROM wcf' . WCF_N . '_user
				WHERE userID = ?';
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array($this->news->userID));
			$row = $statement->fetchArray();

			if ($row !== false && $row['registrationIpAddress']) {
				$registrationIpAddress = UserUtil::convertIPv6To4($row['registrationIpAddress']);
				WCF::getTPL()->assign(array(
					'registrationIpAddress' => array(
						'hostname' => @gethostbyaddr($registrationIpAddress),
						'ipAddress' => $registrationIpAddress,
					),
				));
			}
		}

		WCF::getTPL()->assign(array(
			'authorIpAddresses' => $authorIpAddresses,
			'ipAddress' => array(
				'hostname' => @gethostbyaddr($ipAddress),
				'ipAddress' => $ipAddress,
			),
			'otherUsers' => $otherUsers,
			'news' => $this->news,
		));

		return array(
			'newsID' => $this->news->newsID,
			'template' => WCF::getTPL()->fetch('newsIpAddress', 'cms'),
		);
	}

	/**
	 * Validates parameters and permissions to get a news preview.
	 *
	 * @throws \wcf\system\exception\UserInputException
	 */
	public function validateGetNewsPreview() {
		$this->news = $this->getSingleObject();

		foreach ($this->news->getCategories() as $category) {
			$category->getPermission('canViewNews');
		}
	}

	/**
	 * Return the preview of a news.
	 *
	 * @return array
	 */
	public function getNewsPreview() {
		// why did i use viewable list, when having a news object ???
		WCF::getTPL()->assign(array('news' => new ViewableNews($this->news->getDecoratedObject()),));

		return array('template' => WCF::getTPL()->fetch('newsPreview', 'cms'),);
	}

	/**
	 * {@inheritdoc}
	 */
	public function validateUnmarkAll() {
	}

	/**
	 * {@inheritdoc}
	 */
	public function unmarkAll() {
		ClipboardHandler::getInstance()->removeItems(ClipboardHandler::getInstance()->getObjectTypeID('de.codequake.cms.news'));
	}

	/**
	 * @param array $objectIDs
	 *
	 * @throws \wcf\system\exception\SystemException
	 */
	protected function unmarkItems(array $objectIDs = array()) {
		if (0 === count($objectIDs)) {
			foreach ($this->objects as $news) {
				$objectIDs[] = $news->newsID;
			}
		}

		if (0 !== count($objectIDs)) {
			ClipboardHandler::getInstance()->unmark($objectIDs,
				ClipboardHandler::getInstance()->getObjectTypeID('de.codequake.cms.news'));
		}
	}
}
