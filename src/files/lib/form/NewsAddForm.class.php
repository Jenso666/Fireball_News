<?php

namespace cms\form;

use cms\data\category\NewsCategory;
use cms\data\category\NewsCategoryCache;
use cms\data\category\NewsCategoryNodeTree;
use cms\data\file\FileCache;
use cms\data\news\NewsAction;
use cms\data\news\NewsEditor;
use cms\system\label\object\NewsLabelObjectHandler;
use wcf\data\category\CategoryList;
use wcf\data\user\UserProfile;
use wcf\form\MessageForm;
use wcf\system\category\CategoryHandler;
use wcf\system\exception\UserInputException;
use wcf\system\label\LabelHandler;
use wcf\system\language\LanguageFactory;
use wcf\system\poll\PollManager;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\DateUtil;
use wcf\util\HeaderUtil;
use wcf\util\JSON;
use wcf\util\StringUtil;

/**
 * Shows the news add form.
 *
 * @author      Jens Krumsieck, Florian Gail
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
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
	
	/**
	 * @var integer|\DateTime
	 */
	public $time = 0;

	public $teaser = '';

	public $tags = [];

	public $showSignature = 0;

	public $authors = '';
	
	/**
	 * @var	\wcf\data\label\group\ViewableLabelGroup[]
	 */
	public $labelGroups;
	
	/**
	 * @var	integer[][]
	 */
	public $labelGroupIDsByCategory;
	
	/**
	 * label ids
	 * @var	integer[]
	 */
	public $labelIDs = array();
	
	/**
	 * @inheritDoc
	 */
	public $maxTextLength = FIREBALL_NEWS_MESSAGE_MAXLENGTH;

	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['labelIDs']) && is_array($_POST['labelIDs'])) $this->labelIDs = $_POST['labelIDs'];

		if (isset($_POST['categoryIDs']) && is_array($_POST['categoryIDs'])) $this->categoryIDs = ArrayUtil::trim($_POST['categoryIDs']);
		if (isset($_POST['tags']) && is_array($_POST['tags'])) $this->tags = ArrayUtil::trim($_POST['tags']);
		if (isset($_POST['time']) && !empty($_POST['time'])) $this->time = \DateTime::createFromFormat('Y-m-d\TH:i:sP', $_POST['time'], WCF::getUser()->getTimeZone());
		if (isset($_POST['imageID'])) $this->imageID = intval($_POST['imageID']);
		if (isset($_POST['teaser'])) $this->teaser = StringUtil::trim($_POST['teaser']);
		if (isset($_POST['showSignature'])) $this->showSignature = 1;
		if (isset($_POST['authors'])) $this->authors = StringUtil::trim($_POST['authors']);
		
		if (is_bool($this->time) || is_numeric($this->time)) $this->time = 0;

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
		
		$this->labelGroupIDsByCategory = NewsCategoryCache::getInstance()->getLabelGroupIDs();
		$labelGroupIDs = array();
		foreach ($this->labelGroupIDsByCategory as $categoryID => $groupIDs) {
			$labelGroupIDs = array_merge($labelGroupIDs, $groupIDs);
		}
		array_unique($labelGroupIDs);
		if (!empty($labelGroupIDs)) {
			$this->labelGroups = LabelHandler::getInstance()->getLabelGroups($labelGroupIDs);
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
		
		if (FIREBALL_NEWS_DISCLAIMER && ((FIREBALL_NEWS_DISCLAIMER_GUESTS && !WCF::getUser()->userID) || (FIREBALL_NEWS_DISCLAIMER_USERS && WCF::getUser()->userID))) {
			if (!isset($_POST['disclaimerAccepted'])) {
				throw new UserInputException('disclaimerAccepted');
			}
		}
		
		$validationResult = NewsLabelObjectHandler::getInstance()->validateLabelIDs($this->labelIDs, 'canSetLabel', false);
		if (!empty($validationResult[0])) {
			throw new UserInputException('labelIDs');
		}
		
		if (!empty($validationResult)) {
			throw new UserInputException('label', $validationResult);
		}
	}

	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();

		if (!empty($this->authors)) {
			$authorIDs = UserProfile::getUserProfilesByUsername(ArrayUtil::trim(explode(',', $this->authors)));
			$authorIDs = array_unique($authorIDs);
		}

		$data = [
			'languageID' => $this->languageID,
			'subject' => $this->subject,
			'time' => ($this->time instanceof \DateTime) ? $this->time->getTimestamp() : ($this->time > 0 ? $this->time : TIME_NOW),
			'teaser' => $this->teaser,
			'message' => $this->text,
			'userID' => WCF::getUser()->userID ?: null,
			'username' => WCF::getUser()->username,
			'isDelayed' => (($this->time instanceof \DateTime && $this->time->getTimestamp() > TIME_NOW) || $this->time > TIME_NOW) ? 1 : 0,
			'showSignature' => $this->showSignature,
			'imageID' => $this->imageID ? : null,
			'lastChangeTime' => TIME_NOW,
			'hasLabels' => !empty($this->labelIDs) ? 1 : 0
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
		
		// save labels
		if (!empty($this->labelIDs)) {
			NewsLabelObjectHandler::getInstance()->setLabels($this->labelIDs, $resultValues['returnValues']->newsID);
		}

		$this->saved();
		
		if (WCF::getSession()->getPermission('user.fireball.news.canAddNewsWithoutModeration')) {
			HeaderUtil::redirect($resultValues['returnValues']->getLink());
		} else {
			HeaderUtil::delayedRedirect(LinkHandler::getInstance()->getLink('NewsOverview', array('application' => 'cms')), WCF::getLanguage()->get('cms.news.moderation.disabledNews'));
		}
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
		
		WCF::getTPL()->assign([
			'categoryList' => $this->categoryList,
			'categoryIDs' => $this->categoryIDs,
			'imageID' => $this->imageID,
			'image' => $this->image,
			'teaser' => $this->teaser,
			'time' => ($this->time instanceof \DateTime) ? $this->time->format('c') : $this->time,
			'action' => $this->action,
			'tags' => $this->tags,
			'allowedFileExtensions' => explode("\n", StringUtil::unifyNewlines(WCF::getSession()->getPermission('user.fireball.news.allowedAttachmentExtensions'))),
			'authors' => $this->authors,
			'labelGroups' => $this->labelGroups,
			'labelIDs' => $this->labelIDs,
			'labelGroupIDsByCategory' => JSON::encode($this->labelGroupIDsByCategory)
		]);
	}
}
