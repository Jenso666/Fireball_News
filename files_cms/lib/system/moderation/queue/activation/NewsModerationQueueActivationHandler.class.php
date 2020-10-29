<?php

namespace cms\system\moderation\queue\activation;
use cms\data\news\NewsAction;
use cms\data\news\ViewableNews;
use cms\system\moderation\queue\AbstractNewsModerationQueueHandler;
use wcf\data\moderation\queue\ModerationQueue;
use wcf\data\moderation\queue\ViewableModerationQueue;
use wcf\system\moderation\queue\activation\IModerationQueueActivationHandler;
use wcf\system\WCF;

/**
 * @author      Florian Gail
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
 */
class NewsModerationQueueActivationHandler extends AbstractNewsModerationQueueHandler implements IModerationQueueActivationHandler {
	/**
	 * @inheritDoc
	 */
	protected $definitionName = 'com.woltlab.wcf.moderation.activation';

	/**
	 * @inheritDoc
	 */
	public function enableContent(ModerationQueue $queue) {
		if ($this->isValid($queue->objectID) && $this->getNews($queue->objectID)->isDisabled) {
			$newsAction = new NewsAction([$this->getNews($queue->objectID)], 'enable');
			$newsAction->executeAction();
		}
	}

	/**
	 * @inheritDoc
	 */
	public function getDisabledContent(ViewableModerationQueue $queue) {
		$news = new ViewableNews($queue->getAffectedObject());

		// return template
		return WCF::getTPL()->fetch('moderationNews', 'cms', [
			'news' => $news
		]);
	}
}
