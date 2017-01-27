<?php

/**
 * @author    Jens Krumsieck
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\data\news;

use cms\data\category\NewsCategory;
use cms\data\file\FileCache;
use wcf\data\attachment\GroupedAttachmentList;
use wcf\data\poll\Poll;
use wcf\data\DatabaseObject;
use wcf\data\IMessage;
use wcf\data\IPollObject;
use wcf\system\bbcode\AttachmentBBCode;
use wcf\system\bbcode\MessageParser;
use wcf\system\breadcrumb\Breadcrumb;
use wcf\system\breadcrumb\IBreadcrumbProvider;
use wcf\system\category\CategoryHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\language\LanguageFactory;
use wcf\system\request\IRouteController;
use wcf\system\request\LinkHandler;
use wcf\system\tagging\TagEngine;
use wcf\system\WCF;
use wcf\util\StringUtil;
use wcf\util\UserUtil;

/**
 * Represents a news.
 */
class News extends DatabaseObject implements IMessage, IRouteController, IBreadcrumbProvider, IPollObject {
	protected static $databaseTableName = 'news';

	protected static $databaseTableIndexName = 'newsID';

	protected $categories = null;

	protected $poll = null;

	protected $categoryIDs = array();

	/**
	 * {@inheritdoc}
	 */
	public function getTitle() {
		return $this->subject;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getMessage() {
		return $this->message;
	}

	/**
	 * @return array<\wcf\data\tag\Tag>
	 */
	public function getTags() {
		$tags = TagEngine::getInstance()->getObjectTags('de.codequake.cms.news', $this->newsID,
			array(($this->languageID === null ? LanguageFactory::getInstance()->getDefaultLanguageID() : ''),));

		return $tags;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFormattedMessage() {
		AttachmentBBCode::setObjectID($this->newsID);

		MessageParser::getInstance()->setOutputType('text/html');

		return MessageParser::getInstance()->parse($this->getMessage(), $this->enableSmilies, $this->enableHtml,
			$this->enableBBCodes);
	}

	/**
	 * Returns a simplified version of the formatted message.
	 *
	 * @return string
	 */
	public function getSimplifiedFormattedMessage() {
		MessageParser::getInstance()->setOutputType('text/simplified-html');

		return MessageParser::getInstance()->parse($this->getMessage(), $this->enableSmilies, $this->enableHtml,
			$this->enableBBCodes);
	}

	/**
	 * @return \wcf\data\attachment\GroupedAttachmentList
	 */
	public function getAttachments() {
		if (MODULE_ATTACHMENT == 1 && $this->attachments) {
			$attachmentList = new GroupedAttachmentList('de.codequake.cms.news');
			$attachmentList->getConditionBuilder()->add('attachment.objectID IN (?)', array($this->newsID,));
			$attachmentList->readObjects();
			$attachmentList->setPermissions(array(
				'canDownload' => WCF::getSession()->getPermission('user.fireball.news.canDownloadAttachments'),
				'canViewPreview' => WCF::getSession()->getPermission('user.fireball.news.canDownloadAttachments'),
			));

			AttachmentBBCode::setAttachmentList($attachmentList);

			return $attachmentList;
		}

		return;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getExcerpt($maxLength = FIREBALL_NEWS_TRUNCATE_PREVIEW) {
		return StringUtil::truncateHTML($this->getSimplifiedFormattedMessage(), $maxLength);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getUserID() {
		return $this->userID;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getUsername() {
		return $this->username;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTime() {
		return $this->time;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param bool $appendSession
	 */
	public function getLink($appendSession = true) {
		return LinkHandler::getInstance()->getLink('News', array(
			'application' => 'cms',
			'object' => $this,
			'appendSession' => $appendSession,
			'forceFrontend' => true,
		));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getLanguage() {
		if ($this->languageID) {
			return LanguageFactory::getInstance()->getLanguage($this->languageID);
		}

		return;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getLanguageIcon() {
		return '<img src="' . $this->getLanguage()->getIconPath() . '" alt="" title="' . $this->getLanguage() . '" class="jsTooltip iconFlag" />';
	}

	/**
	 * {@inheritdoc}
	 */
	public function __toString() {
		return $this->getFormattedMessage();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getBreadcrumb() {
		return new Breadcrumb($this->subject, $this->getLink());
	}

	/**
	 * @return int[]
	 */
	public function getCategoryIDs() {
		return $this->categoryIDs;
	}

	/**
	 * @param int $categoryID
	 */
	public function setCategoryID($categoryID) {
		$this->categoryIDs[] = $categoryID;
	}

	/**
	 * @param int[] $categoryIDs
	 */
	public function setCategoryIDs(array $categoryIDs) {
		$this->categoryIDs = $categoryIDs;
	}

	/**
	 * @return array<\cms\data\category\NewsCategory>
	 */
	public function getCategories() {
		if ($this->categories === null) {
			$this->categories = array();

			if (0 !== count($this->categoryIDs)) {
				foreach ($this->categoryIDs as $categoryID) {
					$this->categories[$categoryID] = new NewsCategory(CategoryHandler::getInstance()->getCategory($categoryID));
				}
			}
			else {
				$sql = '
                    SELECT categoryID
                    FROM cms' . WCF_N . '_news_to_category
                    WHERE newsID = ?';
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute(array($this->newsID));

				while ($row = $statement->fetchArray()) {
					$this->categories[$row['categoryID']] = new NewsCategory(CategoryHandler::getInstance()->getCategory($row['categoryID']));
				}
			}
		}

		return $this->categories;
	}

	/**
	 * @return string
	 */
	public function getIpAddress() {
		if ($this->ipAddress) {
			return UserUtil::convertIPv6To4($this->ipAddress);
		}

		return '';
	}

	/**
	 * {@inheritdoc}
	 */
	public function isVisible() {
		return true;
	}

	/**
	 * @return bool
	 */
	public function canRead() {
		return WCF::getSession()->getPermission('user.fireball.news.canViewCategory');
	}

	/**
	 * @return bool
	 */
	public function canAdd() {
		return WCF::getSession()->getPermission('user.fireball.news.canAddNews');
	}

	/**
	 * @return bool
	 */
	public function canModerate() {
		return WCF::getSession()->getPermission('mod.fireball.news.canModerateNews');
	}

	/**
	 * @return bool
	 */
	public function canSeeDelayed() {
		foreach ($this->getCategories() as $category) {
			if (!$category->getPermission('canViewDelayedNews')) {
				return false;
			}
		}

		return true;
	}

	/**
	 * @return \cms\data\file\File
	 */
	public function getImage() {
		if ($this->imageID != 0) {
			return FileCache::getInstance()->getFile($this->imageID);
		}

		return;
	}

	/**
	 * @param int $userID
	 * @param string $username
	 * @param string $notIpAddress
	 * @param int $limit
	 *
	 * @return string[]
	 *
	 * @throws \wcf\system\database\DatabaseException
	 */
	public static function getIpAddressByAuthor($userID, $username = '', $notIpAddress = '', $limit = 10) {
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add('userID = ?', array($userID));

		if ($username !== '' && !$userID) {
			$conditions->add('username = ?', array($username));
		}

		if ($notIpAddress !== '') {
			$conditions->add('ipAddress <> ?', array($notIpAddress));
		}

		$conditions->add("ipAddress <> ''");

		$sql = '
            SELECT DISTINCT ipAddress
            FROM cms' . WCF_N . '_news
            ' . $conditions . '
            ORDER BY time DESC';
		$statement = WCF::getDB()->prepareStatement($sql, $limit);
		$statement->execute($conditions->getParameters());

		$ipAddresses = array();
		while ($row = $statement->fetchArray()) {
			$ipAddresses[] = $row['ipAddress'];
		}

		return $ipAddresses;
	}

	/**
	 * @param string $ipAddress
	 * @param int    $notUserID
	 * @param string $notUsername
	 * @param int    $limit
	 *
	 * @return array
	 */
	public static function getAuthorByIpAddress($ipAddress, $notUserID = 0, $notUsername = '', $limit = 10) {
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add('ipAddress = ?', array($ipAddress));

		if ($notUserID) {
			$conditions->add('userID <> ?', array($notUserID));
		}

		if ($notUsername !== '') {
			$conditions->add('username <> ?', array($notUsername));
		}

		$sql = '
            SELECT DISTINCT username, userID
            FROM cms' . WCF_N . '_news
            ' . $conditions . '
            ORDER BY time DESC';
		$statement = WCF::getDB()->prepareStatement($sql, $limit);
		$statement->execute($conditions->getParameters());

		$users = array();
		while ($row = $statement->fetchArray()) {
			$users[] = $row;
		}

		return $users;
	}

	/**
	 * @return \wcf\data\poll\Poll
	 */
	public function getPoll() {
		if ($this->pollID && $this->poll === null) {
			$this->poll = new Poll($this->pollID);
			$this->poll->setRelatedObject($this);
		}

		return $this->poll;
	}

	/**
	 * @param \wcf\data\poll\Poll $poll
	 */
	public function setPoll(Poll $poll) {
		$this->poll = $poll;
		$this->poll->setRelatedObject($this);
	}

	/**
	 * {@inheritdoc}
	 */
	public function canVote() {
		return (WCF::getSession()->getPermission('user.fireball.news.canVotePoll') ? true : false);
	}
}
