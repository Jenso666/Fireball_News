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
use wcf\system\breadcrumb\Breadcrumb;
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
	public $newsID = 0;

	public $news;

	/**
	 * {@inheritdoc}
	 */
	public $templateName = 'newsAdd';

	/**
	 * {@inheritdoc}
	 */
	public $action = 'edit';

	/**
	 * {@inheritdoc}
	 */
	public $tags = array();

	/**
	 * {@inheritdoc}
	 *
	 * @throws \wcf\system\exception\IllegalLinkException if no id provided with this request or no news with the given
	 *                                                    id exists.
	 */
	public function readParameters() {
		parent::readParameters();

		if (isset($_REQUEST['id'])) {
			$this->newsID = intval($_REQUEST['id']);
		}

		if ($this->newsID == 0) {
			throw new IllegalLinkException();
		}

		// set attachment object id
		$this->attachmentObjectID = $this->newsID;
	}

	/**
	 * {@inheritdoc}
	 */
	public function readData() {
		parent::readData();

		$this->news = new News($this->newsID);

		if (WCF::getSession()->getPermission('user.cms.news.canStartPoll') && MODULE_POLL) {
			PollManager::getInstance()->setObject('de.codequake.cms.news', $this->news->newsID, $this->news->pollID);
		}

		$time = $this->news->time;
		$dateTime = DateUtil::getDateTimeByTimestamp($time);
		$dateTime->setTimezone(WCF::getUser()->getTimeZone());
		$this->time = $dateTime->format('c');

		$this->subject = $this->news->subject;
		$this->teaser = $this->news->teaser;
		$this->text = $this->news->message;
		$this->enableBBCodes = $this->news->enableBBCodes;
		$this->enableHtml = $this->news->enableHtml;
		$this->enableSmilies = $this->news->enableSmilies;
		$this->imageID = $this->news->imageID;

		WCF::getBreadcrumbs()->add(new Breadcrumb($this->news->subject, LinkHandler::getInstance()->getLink('News', array('application' => 'cms', 'object' => $this->news,))));

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

	/**
	 * {@inheritdoc}
	 */
	public function save() {
		MessageForm::save();

		if ($this->time != '') {
			$dateTime = \DateTime::createFromFormat('Y-m-d H:i', $this->time, WCF::getUser()->getTimeZone());
		}

		$data = array('subject' => $this->subject, 'message' => $this->text, 'teaser' => $this->teaser, 'time' => ($this->time != '') ? $dateTime->getTimestamp() : TIME_NOW, 'enableBBCodes' => $this->enableBBCodes, 'showSignature' => $this->showSignature, 'enableHtml' => $this->enableHtml, 'imageID' => $this->imageID ? : null, 'enableSmilies' => $this->enableSmilies, 'lastChangeTime' => TIME_NOW, 'isDisabled' => ($this->time != '' && $dateTime->getTimestamp() > TIME_NOW) ? 1 : 0, 'lastEditor' => WCF::getUser()->username, 'lastEditorID' => WCF::getUser()->userID,);

		$newsData = array('data' => $data, 'categoryIDs' => $this->categoryIDs, 'tags' => $this->tags, 'attachmentHandler' => $this->attachmentHandler,);

		$action = new NewsAction(array($this->newsID), 'update', $newsData);
		$action->executeAction();

		$this->saved();

		// re-define after saving
		$this->news = new News($this->newsID);

		if (WCF::getSession()->getPermission('user.cms.news.canStartPoll') && MODULE_POLL) {
			$pollID = PollManager::getInstance()->save($this->news->newsID);
			if ($pollID && $pollID != $this->news->pollID) {
				$editor = new NewsEditor($this->news);
				$editor->update(array('pollID' => $pollID,));
			}
			elseif (!$pollID && $this->news->pollID) {
				$editor = new NewsEditor($this->news);
				$editor->update(array('pollID' => null,));
			}
		}

		HeaderUtil::redirect(LinkHandler::getInstance()->getLink('News', array('application' => 'cms', 'object' => $this->news,)));
		exit;
	}

	/**
	 * {@inheritdoc}
	 */
	public function assignVariables() {
		parent::assignVariables();

		WCF::getTPL()->assign(array('news' => $this->news, 'newsID' => $this->newsID,));
	}
}
