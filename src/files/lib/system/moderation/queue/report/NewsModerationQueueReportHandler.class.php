<?php

namespace cms\system\moderation\queue\report;

use cms\data\news\ViewableNews;
use cms\system\moderation\queue\AbstractNewsModerationQueueHandler;
use wcf\data\moderation\queue\ViewableModerationQueue;
use wcf\system\moderation\queue\report\IModerationQueueReportHandler;
use wcf\system\WCF;

/**
 * @author      Florian Gail
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
 */
class NewsModerationQueueReportHandler extends AbstractNewsModerationQueueHandler implements IModerationQueueReportHandler {
	/**
	 * @inheritDoc
	 */
	protected $definitionName = 'com.woltlab.wcf.moderation.report';

	/**
	 * @inheritDoc
	 */
	public function canReport($objectID) {
		if (!$this->isValid($objectID)) {
			return false;
		}

		if (!$this->getNews($objectID)->canRead()) {
			return false;
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function getReportedContent(ViewableModerationQueue $queue) {
		$news = new ViewableNews($queue->getAffectedObject());

		// return template
		return WCF::getTPL()->fetch('moderationNews', 'cms', array(
			'news' => $news
		));
	}

	/**
	 * @inheritDoc
	 */
	public function getReportedObject($objectID) {
		if ($this->isValid($objectID)) {
			return $this->getNews($objectID);
		}

		return null;
	}
}
