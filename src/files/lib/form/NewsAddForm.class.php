<?php

/**
 * @author    Jens Krumsieck
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\form;

use cms\data\category\NewsCategory;
use cms\data\category\NewsCategoryNodeTree;
use cms\data\file\FileCache;
use cms\data\news\NewsAction;
use cms\data\news\NewsEditor;
use wcf\data\category\CategoryList;
use wcf\data\user\UserProfile;
use wcf\form\MessageForm;
use wcf\system\category\CategoryHandler;
use wcf\system\exception\UserInputException;
use wcf\system\language\LanguageFactory;
use wcf\system\poll\PollManager;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\DateUtil;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;

/**
 * Shows the news add form.
 */
class NewsAddForm extends MessageForm {
	/**
	 * @inheritDoc
	 */
	public $action = 'add';

	/**
	 * list of category ids
	 * @var integer[]
	 */
	public $categoryIDs = [];

	/**
	 * @var CategoryList
	 */
	public $categoryList = [];

	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['user.fireball.news.canAddNews'];

	/**
	 * @inheritDoc
	 */
	public $enableMultilingualism = true;

	/**
	 * @inheritDoc
	 */
	public $attachmentObjectType = 'de.codequake.cms.news';

	/**
	 * @inheritDoc
	 */
	public $messageObjectType = 'de.codequake.cms.news';

	public $imageID = 0;

	public $image = null;

	public $time = 0;

	public $teaser = '';

	public $tags = [];

	public $showSignature = 0;

	public $authors = '';

	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();

		if (isset($_POST['categoryIDs']) && is_array($_POST['categoryIDs'])) $this->categoryIDs = ArrayUtil::trim($_POST['categoryIDs']);
		if (isset($_POST['tags']) && is_array($_POST['tags'])) $this->tags = ArrayUtil::trim($_POST['tags']);
		if (isset($_POST['time']) && !empty($_POST['time'])) $this->time = \DateTime::createFromFormat('Y-m-d\TH:i:s', $_POST['time'], WCF::getUser()->getTimeZone())->getTimestamp();
		if (isset($_POST['imageID'])) $this->imageID = intval($_POST['imageID']);
		if (isset($_POST['teaser'])) $this->teaser = StringUtil::trim($_POST['teaser']);
		if (isset($_POST['showSignature'])) $this->showSignature = 1;
		if (isset($_POST['authors'])) $this->authors = StringUtil::trim($_POST['authors']);

		if (MODULE_POLL && WCF::getSession()->getPermission('user.fireball.news.canStartPoll')) {
			PollManager::getInstance()->readFormParameters();
		}
	}

	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();

		// polls
		if (MODULE_POLL & WCF::getSession()->getPermission('user.fireball.news.canStartPoll')) {
			PollManager::getInstance()->setObject('de.codequake.cms.news', 0);
		}
	}

	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();

		$excludedCategoryIDs = array_diff(NewsCategory::getAccessibleCategoryIDs(), NewsCategory::getAccessibleCategoryIDs(['canAddNews']));
		$categoryTree = new NewsCategoryNodeTree('de.codequake.cms.category.news', 0, false, $excludedCategoryIDs);
		$this->categoryList = $categoryTree->getIterator();
		$this->categoryList->setMaxDepth(0);

		// default values
		if (empty($_POST)) {
			// multilingualism
			if (0 !== count($this->availableContentLanguages)) {
				if ($this->languageID) {
					$language = LanguageFactory::getInstance()->getUserLanguage();
					$this->languageID = $language->languageID;
				}

				if (!isset($this->availableContentLanguages[$this->languageID])) {
					$languageIDs = array_keys($this->availableContentLanguages);
					$this->languageID = array_shift($languageIDs);
				}
			}
		}
	}

	/**
	 * @inheritDoc
	 */
	public function validate() {
		parent::validate();

		// categories
		if (empty($this->categoryIDs)) {
			throw new UserInputException('categoryIDs');
		}

		foreach ($this->categoryIDs as $categoryID) {
			$category = CategoryHandler::getInstance()->getCategory($categoryID);
			if ($category === null) {
				throw new UserInputException('categoryIDs');
			}

			$category = new NewsCategory($category);
			if (!$category->isAccessible() || !$category->getPermission('canAddNews')) {
				throw new UserInputException('categoryIDs');
			}
		}

		if (MODULE_POLL && WCF::getSession()->getPermission('user.fireball.news.canStartPoll')) {
			PollManager::getInstance()->validate();
		}
	}

	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();

		if ($this->time != '') {
			$dateTime = \DateTime::createFromFormat('Y-m-d H:i', $this->time, WCF::getUser()->getTimeZone());
		}

		if (!empty($this->authors)) {
			$authorIDs = UserProfile::getUserProfilesByUsername(ArrayUtil::trim(explode(',', $this->authors)));
			$authorIDs = array_unique($authorIDs);
		}

		$data = [
			'languageID' => $this->languageID,
			'subject' => $this->subject,
			'time' => $this->time ?: TIME_NOW,
			'teaser' => $this->teaser,
			'message' => $this->text,
			'userID' => WCF::getUser()->userID,
			'username' => WCF::getUser()->username,
			'isDisabled' => ($this->time && $this->time > TIME_NOW) ? 1 : 0,
			'showSignature' => $this->showSignature,
			'imageID' => $this->imageID ? : null,
			'lastChangeTime' => TIME_NOW,
			'authors' => $this->authors
		];

		$newsData = [
			'data' => $data,
			'categoryIDs' => $this->categoryIDs,
			'tags' => $this->tags,
			'attachmentHandler' => $this->attachmentHandler,
			'categoryIDs' => $this->categoryIDs,
			'authorIDs' => empty($authorIDs) ? array() : $authorIDs,
			'htmlInputProcessor' => $this->htmlInputProcessor
		];

		$action = new NewsAction([], 'create', $newsData);
		$resultValues = $action->executeAction();

		// save polls
		if (WCF::getSession()->getPermission('user.fireball.news.canStartPoll') && MODULE_POLL) {
			$pollID = PollManager::getInstance()->save($resultValues['returnValues']->newsID);
			if ($pollID) {
				$editor = new NewsEditor($resultValues['returnValues']);
				$editor->update(['pollID' => $pollID]);
			}
		}

		$this->saved();

		HeaderUtil::redirect(LinkHandler::getInstance()->getLink('News', [
			'application' => 'cms',
			'object' => $resultValues['returnValues'],
		]));
		exit;
	}

	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();

		if (WCF::getSession()->getPermission('user.fireball.news.canStartPoll') && MODULE_POLL) {
			PollManager::getInstance()->assignVariables();
		}

		if ($this->imageID && $this->imageID != 0) {
			$this->image = FileCache::getInstance()->getFile($this->imageID);
		}

		$time = 0;
		if (empty($this->time)) {
			$time = DateUtil::getDateTimeByTimestamp(TIME_NOW);
			$time->setTimezone(WCF::getUser()->getTimeZone());
			$time = $time->format('Y-m-d H:i');
		} else if (!empty($this->time)) {
			$time = DateUtil::getDateTimeByTimestamp($this->time);
			$time->setTimezone(WCF::getUser()->getTimeZone());
			$time = $time->format('Y-m-d H:i');
		} else if (!empty($this->transfer)) {
			$time = DateUtil::getDateTimeByTimestamp($this->transfer->time);
			$time->setTimezone(WCF::getUser()->getTimeZone());
			$time = $time->format('Y-m-d H:i');
		}

		WCF::getTPL()->assign([
			'categoryList' => $this->categoryList,
			'categoryIDs' => $this->categoryIDs,
			'imageID' => $this->imageID,
			'image' => $this->image,
			'teaser' => $this->teaser,
			'time' => $time,
			'action' => $this->action,
			'tags' => $this->tags,
			'allowedFileExtensions' => explode("\n", StringUtil::unifyNewlines(WCF::getSession()->getPermission('user.fireball.news.allowedAttachmentExtensions'))),
			'authors' => $this->authors
		));
	}
}
