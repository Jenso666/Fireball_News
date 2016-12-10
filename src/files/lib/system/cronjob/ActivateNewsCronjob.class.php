<?php

/**
 * @author    Jens Krumsieck
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\system\cronjob;

use cms\data\news\NewsAction;
use cms\data\news\NewsList;
use wcf\data\cronjob\Cronjob;
use wcf\system\cronjob\AbstractCronjob;

/**
 * Activates news when their publication date is reached.
 */
class ActivateNewsCronjob extends AbstractCronjob {
	/**
	 * {@inheritdoc}
	 */
	public function execute(Cronjob $cronjob) {
		parent::execute($cronjob);

		$newsList = new NewsList();
		$newsList->getConditionBuilder()->add('isDisabled = ?', array(1));
		$newsList->getConditionBuilder()->add('time <= ?', array(TIME_NOW));
		$newsList->readObjects();

		$action = new NewsAction($newsList->getObjects(), 'publish');
		$action->executeAction();
	}
}
