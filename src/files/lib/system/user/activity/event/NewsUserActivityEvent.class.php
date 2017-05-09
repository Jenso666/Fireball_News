<?php

namespace cms\system\user\activity\event;

use cms\data\news\NewsList;
use wcf\system\user\activity\event\IUserActivityEvent;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Activity event for created news.
 *
 * @author      Jens Krumsieck, Florian Frantzen
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
 */
class NewsUserActivityEvent extends SingletonFactory implements IUserActivityEvent {
	/**
	 * {@inheritdoc}
	 */
	public function prepare(array $events) {
		$newsIDs = array();
		foreach ($events as $event) {
			$newsIDs[] = $event->objectID;
		}

		$newsList = new NewsList();
		$newsList->setObjectIDs($newsIDs);
		$newsList->readObjects();
		$newsEntries = $newsList->getObjects();

		/** @var \wcf\data\user\activity\event\ViewableUserActivityEvent $event */
		foreach ($events as $event) {
			if (array_key_exists($event->objectID, $newsEntries)) {
				/** @var \cms\data\news\News $news */
				$news = $newsEntries[$event->objectID];

				if (!$news->canRead()) {
					continue;
				}

				$event->setIsAccessible();

				$text = WCF::getLanguage()->getDynamicVariable('wcf.user.profile.recentActivity.news',
					array('news' => $news,));
				$event->setTitle($text);
				$event->setDescription($news->getExcerpt());
			}
			else {
				$event->setIsOrphaned();
			}
		}
	}
}
