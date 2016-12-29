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
	public $categoryIDs = array();

	/**
	 * @var CategoryList
	 */
	public $categoryList = array();

	/**
	 * @inheritDoc
	 */
	public $neededPermissions = array('user.cms.news.canAddNews',);

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

	public $time = '';

	public $teaser = '';

	public $tags = array();

	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();

		if (isset($_POST['tags']) && is_array($_POST['tags'])) $this->tags = ArrayUtil::trim($_POST['tags']);
		if (isset($_POST['time'])) $this->time = \DateTime::createFromFormat('Y-m-d H:i', $_POST['time'], WCF::getUser()->getTimeZone())->getTimestamp();
		if (isset($_POST['imageID'])) $this->imageID = intval($_POST['imageID']);
		if (isset($_POST['teaser'])) $this->teaser = StringUtil::trim($_POST['teaser']);

		if (MODULE_POLL && WCF::getSession()->getPermission('user.cms.news.canStartPoll')) {
			PollManager::getInstance()->readFormParameters();
		}
	}

	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();

		// polls
		if (MODULE_POLL & WCF::getSession()->getPermission('user.cms.news.canStartPoll')) {
			PollManager::getInstance()->setObject('de.codequake.cms.news', 0);
		}

		if (isset($_REQUEST['categoryIDs']) && is_array($_REQUEST['categoryIDs'])) {
			$this->categoryIDs = ArrayUtil::toIntegerArray($_REQUEST['categoryIDs']);
		}
	}

	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();

		$excludedCategoryIDs = array_diff(NewsCategory::getAccessibleCategoryIDs(), NewsCategory::getAccessibleCategoryIDs(array('canAddNews')));
		$categoryTree = new NewsCategoryNodeTree('de.codequake.cms.category.news', 0, false, $excludedCategoryIDs);
		$this->categoryList = $categoryTree->getIterator();
		$this->categoryList->setMaxDepth(0);

		if (empty($_POST)) {
			$dateTime = DateUtil::getDateTimeByTimestamp(TIME_NOW);
			$dateTime->setTimezone(WCF::getUser()->getTimeZone());
			$this->time = $dateTime->format('c');
		}
		else {
			$dateTime = DateUtil::getDateTimeByTimestamp(@strtotime($this->time));
			$dateTime->setTimezone(WCF::getUser()->getTimeZone());
			$this->time = $dateTime->format('c');
		}

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
		if (!empty($this->categoryIDs)) {
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

		if (MODULE_POLL && WCF::getSession()->getPermission('user.cms.news.canStartPoll')) {
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

		$data = array(
			'languageID' => $this->languageID,
			'subject' => $this->subject,
			'time' => ($this->time != '') ? $dateTime->getTimestamp() : TIME_NOW,
			'teaser' => $this->teaser,
			'message' => $this->text,
			'userID' => WCF::getUser()->userID,
			'username' => WCF::getUser()->username,
			'isDisabled' => ($this->time != '' && $dateTime->getTimestamp() > TIME_NOW) ? 1 : 0,
			'enableBBCodes' => $this->enableBBCodes,
			'showSignature' => $this->showSignature,
			'enableHtml' => $this->enableHtml,
			'enableSmilies' => $this->enableSmilies,
			'imageID' => $this->imageID ? : null,
			'lastChangeTime' => TIME_NOW,
		);

		$newsData = array(
			'data' => $data,
			'tags' => $this->tags,
			'attachmentHandler' => $this->attachmentHandler,
			'categoryIDs' => $this->categoryIDs,
		);

		$action = new NewsAction(array(), 'create', $newsData);
		$resultValues = $action->executeAction();

		// save polls
		if (WCF::getSession()->getPermission('user.cms.news.canStartPoll') && MODULE_POLL) {
			$pollID = PollManager::getInstance()->save($resultValues['returnValues']->newsID);
			if ($pollID) {
				$editor = new NewsEditor($resultValues['returnValues']);
				$editor->update(array('pollID' => $pollID,));
			}
		}

		$this->saved();

		HeaderUtil::redirect(LinkHandler::getInstance()->getLink('News', array(
			'application' => 'cms',
			'object' => $resultValues['returnValues'],
		)));
		exit;
	}

	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();

		if (WCF::getSession()->getPermission('user.cms.news.canStartPoll') && MODULE_POLL) {
			PollManager::getInstance()->assignVariables();
		}

		if ($this->imageID && $this->imageID != 0) {
			$this->image = FileCache::getInstance()->getFile($this->imageID);
		}

		WCF::getTPL()->assign(array(
			'categoryList' => $this->categoryList,
			'categoryIDs' => $this->categoryIDs,
			'imageID' => $this->imageID,
			'image' => $this->image,
			'teaser' => $this->teaser,
			'time' => $this->time,
			'action' => $this->action,
			'tags' => $this->tags,
			'allowedFileExtensions' => explode("\n",
				StringUtil::unifyNewlines(WCF::getSession()->getPermission('user.cms.news.allowedAttachmentExtensions'))),
		));
	}
}
