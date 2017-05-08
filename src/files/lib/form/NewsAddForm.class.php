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
use wcf\data\user\UserProfile;
use wcf\form\MessageForm;
use wcf\system\breadcrumb\Breadcrumb;
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
	 * {@inheritdoc}
	 */
	public $action = 'add';

	public $categoryIDs = array();

	public $categoryList = array();

	/**
	 * {@inheritdoc}
	 */
	public $activeMenuItem = 'cms.page.news';

	/**
	 * {@inheritdoc}
	 */
	public $enableTracking = true;

	/**
	 * {@inheritdoc}
	 */
	public $neededPermissions = array('user.fireball.news.canAddNews',);

	/**
	 * {@inheritdoc}
	 */
	public $enableMultilingualism = true;

	/**
	 * {@inheritdoc}
	 */
	public $attachmentObjectType = 'de.codequake.cms.news';

	public $imageID = 0;

	public $image = null;

	public $time = '';

	public $teaser = '';

	public $tags = array();

	public $authors = '';
	
	/**
	 * @inheritDoc
	 */
	public $maxTextLength = FIREBALL_NEWS_MESSAGE_MAXLENGTH;

	/**
	 * {@inheritdoc}
	 */
	public function readFormParameters() {
		parent::readFormParameters();

		if (isset($_POST['tags']) && is_array($_POST['tags'])) {
			$this->tags = ArrayUtil::trim($_POST['tags']);
		}
		if (isset($_POST['time'])) {
			$this->time = $_POST['time'];
		}
		if (isset($_POST['imageID'])) {
			$this->imageID = intval($_POST['imageID']);
		}
		if (isset($_POST['teaser'])) {
			$this->teaser = StringUtil::trim($_POST['teaser']);
		}

		if (isset($_POST['authors'])) $this->authors = StringUtil::trim($_POST['authors']);

		if (MODULE_POLL && WCF::getSession()->getPermission('user.fireball.news.canStartPoll')) {
			PollManager::getInstance()->readFormParameters();
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function readParameters() {
		parent::readParameters();
		// polls
		if (MODULE_POLL & WCF::getSession()->getPermission('user.fireball.news.canStartPoll')) {
			PollManager::getInstance()->setObject('de.codequake.cms.news', 0);
		}
		if (isset($_REQUEST['categoryIDs']) && is_array($_REQUEST['categoryIDs'])) {
			$this->categoryIDs = ArrayUtil::toIntegerArray($_REQUEST['categoryIDs']);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function readData() {
		parent::readData();

		WCF::getBreadcrumbs()->add(new Breadcrumb(WCF::getLanguage()->get('cms.page.news'),
			LinkHandler::getInstance()->getLink('NewsOverview', array('application' => 'cms',))));

		$excludedCategoryIDs = array_diff(NewsCategory::getAccessibleCategoryIDs(),
			NewsCategory::getAccessibleCategoryIDs(array('canAddNews',)));
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
			$this->username = WCF::getSession()->getVar('username');

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
	 * {@inheritdoc}
	 */
	public function validate() {
		parent::validate();

		// categories
		if (0 === count($this->categoryIDs)) {
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
		
		if (FIREBALL_NEWS_DISCLAIMER && ((FIREBALL_NEWS_DISCLAIMER_GUESTS && !WCF::getUser()->userID) || (FIREBALL_NEWS_DISCLAIMER_USERS && WCF::getUser()->userID))) {
			if (!isset($_POST['disclaimerAccepted'])) {
				throw new UserInputException('disclaimerAccepted');
			}
		}
	}

	/**
	 * {@inheritdoc}
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

		$data = array(
			'languageID' => $this->languageID,
			'subject' => $this->subject,
			'time' => ($this->time != '') ? $dateTime->getTimestamp() : TIME_NOW,
			'teaser' => $this->teaser,
			'message' => $this->text,
			'userID' => WCF::getUser()->userID ?: null,
			'username' => WCF::getUser()->username,
			'isDelayed' => ($this->time != '' && $dateTime->getTimestamp() > TIME_NOW) ? 1 : 0,
			'enableBBCodes' => $this->enableBBCodes,
			'showSignature' => $this->showSignature,
			'enableHtml' => $this->enableHtml,
			'enableSmilies' => $this->enableSmilies,
			'imageID' => $this->imageID ? : null,
			'lastChangeTime' => TIME_NOW
		);

		$newsData = array(
			'data' => $data,
			'tags' => $this->tags,
			'attachmentHandler' => $this->attachmentHandler,
			'categoryIDs' => $this->categoryIDs,
			'authorIDs' => empty($authorIDs) ? array() : $authorIDs
		);

		$action = new NewsAction(array(), 'create', $newsData);
		$resultValues = $action->executeAction();

		// save polls
		if (WCF::getSession()->getPermission('user.fireball.news.canStartPoll') && MODULE_POLL) {
			$pollID = PollManager::getInstance()->save($resultValues['returnValues']->newsID);
			if ($pollID) {
				$editor = new NewsEditor($resultValues['returnValues']);
				$editor->update(array('pollID' => $pollID,));
			}
		}

		$this->saved();
		
		if (!WCF::getSession()->getPermission('user.fireball.news.canAddNewsWithoutModeration')) {
			HeaderUtil::redirect($resultValues['returnValues']->getLink());
		} else {
			HeaderUtil::delayedRedirect(LinkHandler::getInstance()->getLink('NewsOverview', array('application' => 'cms')), WCF::getLanguage()->get('cms.news.redirect.moderation'));
		}
		exit;
	}

	/**
	 * {@inheritdoc}
	 */
	public function assignVariables() {
		parent::assignVariables();

		if (WCF::getSession()->getPermission('user.fireball.news.canStartPoll') && MODULE_POLL) {
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
			'allowedFileExtensions' => explode("\n", StringUtil::unifyNewlines(WCF::getSession()->getPermission('user.fireball.news.allowedAttachmentExtensions'))),
			'authors' => $this->authors
		));
	}
}
