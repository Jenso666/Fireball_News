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
use wcf\data\ITitledLinkObject;
use wcf\data\TUserContent;
use wcf\system\bbcode\AttachmentBBCode;
use wcf\system\category\CategoryHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\html\output\HtmlOutputProcessor;
use wcf\system\language\LanguageFactory;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\request\IRouteController;
use wcf\system\request\LinkHandler;
use wcf\system\tagging\TagEngine;
use wcf\system\WCF;
use wcf\util\StringUtil;
use wcf\util\UserUtil;

/**
 * Represents a news.
 *
 * @property-read   integer $newsID             id of the news
 * @property-read   integer $userID             id of the author's user
 * @property-read   string  $username           username of the author's user
 * @property-read   string  $subject            subject of the news
 * @property-read   string  $teaser             short teaser (max 255 chars)
 * @property-read   string  $message            message/text of the news
 * @property-read   integer $time               timestamp of publication
 * @property-read   boolean $attachments        news contains attachments
 * @property-read   integer $pollID             id of an inserted poll
 * @property-read   integer $languageID         id of the language the news belongs to
 * @property-read   integer $clicks             click counter
 * @property-read   integer $comments           comment counter
 * @property-read   integer $imageID            id of the news' image
 * @property-read   boolean $enableSmilies      enable Smilies
 * @property-read   boolean $enableHtml         enable HTML
 * @property-read   boolean $enableBBCodes      enable BBCodes
 * @property-read   boolean $showSignature      show the author's signature below the news
 * @property-read   boolean $isDisabled         news is disabled
 * @property-read   boolean $isDeleted          news is trashed
 * @property-read   integer $deleteTime         timestamp the news has been deleted
 * @property-read   integer $lastChangeTime     timestamp of the latest modification
 * @property-read   integer $lastEditor         username of the user who performed the last edit
 * @property-read   integer $lastEditorID       id of the user who performed the last edit
 * @property-read   string  $ipAddress          IP address of the author
 * @property-read   integer $cumulativeLikes    cumulative like counter
 * @property-read   boolean $hasEmbeddedObjects news contains embedded objects
 */
class News extends DatabaseObject implements ITitledLinkObject, IMessage, IRouteController, IPollObject {
	use TUserContent;

	protected $embeddedObjectsLoaded = false;

	/**
	 * list of categories
	 * @var \wcf\data\category\Category[]
	 */
	protected $categories = null;

	/**
	 * embedded poll object
	 * @var \wcf\data\poll\Poll
	 */
	protected $poll = null;

	/**
	 * list of category ids
	 * @var integer[]
	 */
	protected $categoryIDs = [];

	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return WCF::getLanguage()->get($this->subject);
	}

	/**
	 * @inheritDoc
	 */
	public function getMessage() {
		return WCF::getLanguage()->get($this->message);
	}

	/**
	 * @return \wcf\data\tag\Tag[]
	 * @throws \wcf\system\exception\SystemException
	 */
	public function getTags() {
		$tags = TagEngine::getInstance()->getObjectTags('de.codequake.cms.news', $this->newsID,
			[($this->languageID === null ? LanguageFactory::getInstance()->getDefaultLanguageID() : '')]);

		return $tags;
	}

	/**
	 * @inheritDoc
	 */
	public function getFormattedMessage() {
		$this->loadEmbeddedObjects();

		$processor = new HtmlOutputProcessor();
		$processor->process($this->getMessage(), 'de.codequake.cms.news', $this->newsID);

		return $processor->getHtml();
	}

	/**
	 * @inheritDoc
	 */
	public function getSimplifiedFormattedMessage() {
		$processor = new HtmlOutputProcessor();
		$processor->setOutputType('text/simplified-html');
		$processor->process($this->getMessage(), 'de.codequake.cms.news', $this->newsID);

		return $processor->getHtml();
	}

	/**
	 * @return \wcf\data\attachment\GroupedAttachmentList
	 */
	public function getAttachments() {
		if (MODULE_ATTACHMENT == 1 && $this->attachments) {
			$attachmentList = new GroupedAttachmentList('de.codequake.cms.news');
			$attachmentList->getConditionBuilder()->add('attachment.objectID IN (?)', [$this->newsID]);
			$attachmentList->readObjects();
			$attachmentList->setPermissions([
				'canDownload' => WCF::getSession()->getPermission('user.fireball.news.canDownloadAttachments'),
				'canViewPreview' => WCF::getSession()->getPermission('user.fireball.news.canDownloadAttachments'),
			]);

			// set embedded attachments
			AttachmentBBCode::setAttachmentList($attachmentList);

			return $attachmentList;
		}

		return null;
	}

	/**
	 * @inheritDoc
	 */
	public function getExcerpt($maxLength = FIREBALL_NEWS_TRUNCATE_PREVIEW) {
		return StringUtil::truncateHTML($this->getSimplifiedFormattedMessage(), $maxLength);
	}

	/**
	 * Loads the embedded objects.
	 */
	public function loadEmbeddedObjects() {
		if ($this->hasEmbeddedObjects && !$this->embeddedObjectsLoaded) {
			MessageEmbeddedObjectManager::getInstance()->loadObjects('de.codequake.cms.news', [$this->newsID]);
			$this->embeddedObjectsLoaded = true;
		}
	}

	/**
	 * @inheritDoc
	 * @param bool $appendSession
	 */
	public function getLink($appendSession = true) {
		return LinkHandler::getInstance()->getLink('News', [
			'application' => 'cms',
			'object' => $this,
			'appendSession' => $appendSession,
			'forceFrontend' => true,
		]);
	}

	/**
	 * @inheritDoc
	 */
	public function getLanguage() {
		if ($this->languageID) {
			return LanguageFactory::getInstance()->getLanguage($this->languageID);
		}

		return null;
	}

	/**
	 * @inheritDoc
	 */
	public function getLanguageIcon() {
		return '<img src="' . $this->getLanguage()->getIconPath() . '" alt="" title="' . $this->getLanguage() . '" class="jsTooltip iconFlag" />';
	}

	/**
	 * @inheritDoc
	 */
	public function __toString() {
		return $this->getFormattedMessage();
	}

	/**
	 * Returns a list of ids of the news' categories
	 * @return integer[]
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
	 * @return array <\cms\data\category\NewsCategory>
	 * @throws \wcf\system\database\exception\DatabaseQueryException
	 * @throws \wcf\system\database\exception\DatabaseQueryExecutionException
	 * @throws \wcf\system\exception\SystemException
	 */
	public function getCategories() {
		if ($this->categories === null) {
			$this->categories = [];

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
				$statement->execute([$this->newsID]);

				while ($row = $statement->fetchArray()) {
					$this->categories[$row['categoryID']] = new NewsCategory(CategoryHandler::getInstance()->getCategory($row['categoryID']));
				}
			}
		}

		return $this->categories;
	}

	/**
	 * Returns the author's ip address
	 * @return string
	 */
	public function getIpAddress() {
		if (LOG_IP_ADDRESS && $this->ipAddress) {
			return UserUtil::convertIPv6To4($this->ipAddress);
		}

		return '';
	}

	/**
	 * @inheritDoc
	 */
	public function isVisible() {
		return $this->canRead();
	}

	/**
	 * @return boolean
	 */
	public function canRead() {
		return WCF::getSession()->getPermission('user.fireball.news.canViewCategory');
	}

	/**
	 * @return boolean
	 */
	public function canAdd() {
		return WCF::getSession()->getPermission('user.fireball.news.canAddNews');
	}

	/**
	 * @return boolean
	 */
	public function canEdit() {
		return $this->canModerate();
	}

	/**
	 * @return boolean
	 */
	public function canDelete() {
		return $this->canModerate();
	}

	/**
	 * @return boolean
	 */
	public function canModerate() {
		return WCF::getSession()->getPermission('mod.fireball.news.canModerateNews');
	}

	/**
	 * @return bool
	 * @throws \wcf\system\database\exception\DatabaseQueryException
	 * @throws \wcf\system\database\exception\DatabaseQueryExecutionException
	 * @throws \wcf\system\exception\SystemException
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
	 * @throws \wcf\system\exception\SystemException
	 */
	public function getImage() {
		if ($this->imageID != 0) {
			return FileCache::getInstance()->getFile($this->imageID);
		}

		return null;
	}

	/**
	 * @param int    $userID
	 * @param string $username
	 * @param string $notIpAddress
	 * @param int    $limit
	 *
	 * @return string[]
	 *
	 * @throws \wcf\system\database\DatabaseException
	 */
	public static function getIpAddressByAuthor($userID, $username = '', $notIpAddress = '', $limit = 10) {
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add('userID = ?', [$userID]);

		if ($username !== '' && !$userID) {
			$conditions->add('username = ?', [$username]);
		}

		if ($notIpAddress !== '') {
			$conditions->add('ipAddress <> ?', [$notIpAddress]);
		}

		$conditions->add("ipAddress <> ''");

		$sql = '
            SELECT DISTINCT ipAddress
            FROM cms' . WCF_N . '_news
            ' . $conditions . '
            ORDER BY time DESC';
		$statement = WCF::getDB()->prepareStatement($sql, $limit);
		$statement->execute($conditions->getParameters());

		$ipAddresses = [];
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
	 * @return array
	 * @throws \wcf\system\database\exception\DatabaseQueryException
	 * @throws \wcf\system\database\exception\DatabaseQueryExecutionException
	 */
	public static function getAuthorByIpAddress($ipAddress, $notUserID = 0, $notUsername = '', $limit = 10) {
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add('ipAddress = ?', [$ipAddress]);

		if ($notUserID) {
			$conditions->add('userID <> ?', [$notUserID]);
		}

		if ($notUsername !== '') {
			$conditions->add('username <> ?', [$notUsername]);
		}

		$sql = '
            SELECT DISTINCT username, userID
            FROM cms' . WCF_N . '_news
            ' . $conditions . '
            ORDER BY time DESC';
		$statement = WCF::getDB()->prepareStatement($sql, $limit);
		$statement->execute($conditions->getParameters());

		$users = [];
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
	 * @inheritDoc
	 */
	public function canVote() {
		return (WCF::getSession()->getPermission('user.fireball.news.canVotePoll') ? true : false);
	}
}
