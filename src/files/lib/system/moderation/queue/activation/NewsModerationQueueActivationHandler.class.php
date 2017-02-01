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
 * @author	Florian Gail
 * @copyright	2013-2017 codeQuake
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
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
			$newsAction = new NewsAction(array($this->getNews($queue->objectID)), 'enable');
			$newsAction->executeAction();
		}
	}

	/**
	 * @inheritDoc
	 */
	public function getDisabledContent(ViewableModerationQueue $queue) {
		$news = new ViewableNews($queue->getAffectedObject());

		// return template
		return WCF::getTPL()->fetch('moderationNews', 'cms', array(
			'news' => $news
		));
	}
}
