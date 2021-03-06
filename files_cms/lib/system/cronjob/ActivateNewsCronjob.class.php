<?php

namespace cms\system\cronjob;

use cms\data\news\NewsAction;
use cms\data\news\NewsList;
use wcf\data\cronjob\Cronjob;
use wcf\system\cronjob\AbstractCronjob;

/**
 * Activates news when their publication date is reached.
 *
 * @author      Jens Krumsieck, Florian Gail
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
 */
class ActivateNewsCronjob extends AbstractCronjob {
	/**
	 * @inheritDoc
	 */
	public function execute(Cronjob $cronjob) {
		parent::execute($cronjob);

		$newsList = new NewsList();
		$newsList->getConditionBuilder()->add('news.isDelayed = 1');
		$newsList->getConditionBuilder()->add('news.isDisabled = 0');
		$newsList->getConditionBuilder()->add('news.isDeleted = 0');
		$newsList->getConditionBuilder()->add('news.time <= ?', [TIME_NOW]);
		$newsList->readObjects();

		$action = new NewsAction($newsList->getObjects(), 'publish');
		$action->executeAction();
	}
}
