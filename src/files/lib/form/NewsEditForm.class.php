<?php

namespace cms\form;

use cms\data\news\News;
use cms\data\news\NewsAction;
use cms\data\news\NewsEditor;
use cms\system\label\object\NewsLabelObjectHandler;
use wcf\data\user\UserProfile;
use wcf\form\MessageForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\page\PageLocationManager;
use wcf\system\poll\PollManager;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\DateUtil;
use wcf\util\HeaderUtil;

/**
 * Shows the news edit form.
 *
 * @author      Jens Krumsieck, Florian Gail
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
 */
class NewsEditForm extends NewsAddForm {
	/**
	 * id of the news
	 * @var integer
	 */
	public $newsID = 0;

	/**
	 * news object
	 * @var News
	 */
	public $news;

	/**
	 * @inheritDoc
	 */
	public $action = 'edit';

	/**
	 * @inheritDoc
	 */
	public $tags = [];

	/**
	 * @inheritDoc                                      id exists.
	 */
	public function readParameters() {
		parent::readParameters();

		if (isset($_REQUEST['id'])) $this->newsID = intval($_REQUEST['id']);
		$this->news = new News($this->newsID);

		if ($this->news === null || !$this->news->newsID) {
			throw new IllegalLinkException();
		}

		// set attachment object id
		$this->attachmentObjectID = $this->newsID;
	}

	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();

		if (WCF::getSession()->getPermission('user.fireball.news.canStartPoll') && MODULE_POLL) {
			PollManager::getInstance()->setObject('de.codequake.cms.news', $this->news->newsID, $this->news->pollID);
		}

		if (empty($_POST)) {
			$this->time = $this->news->time;
			$this->subject = $this->news->subject;
			$this->teaser = $this->news->teaser;
			$this->text = $this->news->message;
			$this->imageID = $this->news->imageID;
			
			/** @var \wcf\data\user\UserProfile $userProfile */
			foreach ($this->news->getAuthorProfiles() as $userProfile) {
				$this->authors .= (!empty($this->authors) ? ', ' : '') . $userProfile->username;
			}
			
			foreach ($this->news->getCategories() as $category) {
				$this->categoryIDs[] = $category->categoryID;
			}

			// tagging
			if (MODULE_TAGGING) {
				$tags = $this->news->getTags();
				foreach ($tags as $tag) {
					$this->tags[] = $tag->name;
				}
			}
			
			// labels
			$assignedLabels = NewsLabelObjectHandler::getInstance()->getAssignedLabels(array($this->newsID), true);
			if (!empty($assignedLabels[$this->newsID])) {
				foreach ($assignedLabels[$this->newsID] as $label) {
					$this->labelIDs[$label->groupID] = $label->labelID;
				}
			}
		}
	}

	/**
	 * @inheritDoc
	 */
	public function save() {
		MessageForm::save();
		
		NewsLabelObjectHandler::getInstance()->setLabels($this->labelIDs, $this->newsID);
		$labelIDs = NewsLabelObjectHandler::getInstance()->getAssignedLabels(array($this->newsID), false);

		if ($this->time != '') {
			$dateTime = \DateTime::createFromFormat('Y-m-d H:i', $this->time, WCF::getUser()->getTimeZone());
		}
		
		if (!empty($this->authors)) {
			$authorIDs = UserProfile::getUserProfilesByUsername(ArrayUtil::trim(explode(',', $this->authors)));
			$authorIDs = array_unique($authorIDs);
		}

		$data = [
			'subject' => $this->subject,
			'message' => $this->text,
			'teaser' => $this->teaser,
			'time' => $this->time ?: TIME_NOW,
			'showSignature' => $this->showSignature,
			'imageID' => $this->imageID ? : null,
			'lastChangeTime' => TIME_NOW,
			'isDelayed' => ($this->time && $this->time > TIME_NOW) ? 1 : 0,
			'lastEditor' => WCF::getUser()->username,
			'lastEditorID' => WCF::getUser()->userID ?: null,
			'hasLabels' => !empty($labelIDs[$this->newsID]) ? 1 : 0
		];

		$newsData = [
			'data' => $data,
			'categoryIDs' => $this->categoryIDs,
			'tags' => $this->tags,
			'attachmentHandler' => $this->attachmentHandler,
			'htmlInputProcessor' => $this->htmlInputProcessor,
			'authorIDs' => empty($authorIDs) ? [] : $authorIDs
		];

		$action = new NewsAction([$this->newsID], 'update', $newsData);
		$action->executeAction();

		$this->saved();

		// re-define after saving
		$this->news = new News($this->newsID);

		if (WCF::getSession()->getPermission('user.fireball.news.canStartPoll') && MODULE_POLL) {
			$pollID = PollManager::getInstance()->save($this->news->newsID);
			if ($pollID && $pollID != $this->news->pollID) {
				$editor = new NewsEditor($this->news);
				$editor->update(['pollID' => $pollID]);
			}
			else if (!$pollID && $this->news->pollID) {
				$editor = new NewsEditor($this->news);
				$editor->update(['pollID' => null]);
			}
		}

		HeaderUtil::redirect(LinkHandler::getInstance()->getLink('News', [
			'application' => 'cms',
			'object' => $this->news,
		]));
		exit;
	}

	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();

		PageLocationManager::getInstance()->addParentLocation('de.codequake.cms.news.News', null, $this->news);

		WCF::getTPL()->assign([
			'news' => $this->news,
			'newsID' => $this->newsID,
		]);
	}
}
