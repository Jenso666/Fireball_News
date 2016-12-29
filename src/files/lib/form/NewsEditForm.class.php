<?php

/**
 * @author    Jens Krumsieck
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\form;

use cms\data\news\News;
use cms\data\news\NewsAction;
use cms\data\news\NewsEditor;
use wcf\form\MessageForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\poll\PollManager;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\DateUtil;
use wcf\util\HeaderUtil;

/**
 * Shows the news edit form.
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
	public $tags = array();

	/**
	 * @inheritDoc
	 *
	 * @throws \wcf\system\exception\IllegalLinkException if no id provided with this request or no news with the given
	 *                                                    id exists.
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
		}
	}

	/**
	 * @inheritDoc
	 */
	public function save() {
		MessageForm::save();

		$data = array(
			'subject' => $this->subject,
			'message' => $this->text,
			'teaser' => $this->teaser,
			'time' => $this->time ?: TIME_NOW,
			'showSignature' => $this->showSignature,
			'imageID' => $this->imageID ? : null,
			'lastChangeTime' => TIME_NOW,
			'isDisabled' => ($this->time && $this->time > TIME_NOW) ? 1 : 0,
			'lastEditor' => WCF::getUser()->username,
			'lastEditorID' => WCF::getUser()->userID,
		);

		$newsData = array(
			'data' => $data,
			'categoryIDs' => $this->categoryIDs,
			'tags' => $this->tags,
			'attachmentHandler' => $this->attachmentHandler,
			'htmlInputProcessor' => $this->htmlInputProcessor
		);

		$action = new NewsAction(array($this->newsID), 'update', $newsData);
		$action->executeAction();

		$this->saved();

		// re-define after saving
		$this->news = new News($this->newsID);

		if (WCF::getSession()->getPermission('user.fireball.news.canStartPoll') && MODULE_POLL) {
			$pollID = PollManager::getInstance()->save($this->news->newsID);
			if ($pollID && $pollID != $this->news->pollID) {
				$editor = new NewsEditor($this->news);
				$editor->update(array('pollID' => $pollID,));
			}
			else if (!$pollID && $this->news->pollID) {
				$editor = new NewsEditor($this->news);
				$editor->update(array('pollID' => null,));
			}
		}

		HeaderUtil::redirect(LinkHandler::getInstance()->getLink('News', array(
			'application' => 'cms',
			'object' => $this->news,
		)));
		exit;
	}

	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();

		WCF::getTPL()->assign(array(
			'news' => $this->news,
			'newsID' => $this->newsID,
		));
	}
}
