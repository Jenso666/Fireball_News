<?php

namespace cms\system\user\activity\event;

use cms\data\news\NewsList;
use wcf\data\comment\CommentList;
use wcf\system\user\activity\event\IUserActivityEvent;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Activity event for news comments.
 *
 * @author      Jens Krumsieck, Florian Frantzen
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
 */
class NewsCommentUserActivityEvent extends SingletonFactory implements IUserActivityEvent {
	/**
	 * @inheritDoc
	 */
	public function prepare(array $events) {
		$commentIDs = $newsIDs = [];

		foreach ($events as $event) {
			$commentIDs[] = $event->objectID;
		}

		$commentList = new CommentList();
		$commentList->setObjectIDs($commentIDs);
		$commentList->readObjects();
		$comments = $commentList->getObjects();

		foreach ($comments as $comment) {
			$newsIDs[] = $comment->objectID;
		}

		$newsList = new NewsList();
		$newsList->setObjectIDs($newsIDs);
		$newsList->readObjects();
		$newsEntries = $newsList->getObjects();

		/** @var \wcf\data\user\activity\event\ViewableUserActivityEvent $event */
		foreach ($events as $event) {
			if (array_key_exists($event->objectID, $comments)) {
				/** @var \wcf\data\comment\Comment $comment */
				$comment = $comments[$event->objectID];

				if (array_key_exists($comment->objectID, $newsEntries)) {
					/** @var \cms\data\news\News $news */
					$news = $newsEntries[$comment->objectID];

					if (!$news->canRead()) {
						continue;
					}

					$event->setIsAccessible();

					$text = WCF::getLanguage()->getDynamicVariable('wcf.user.profile.recentActivity.newsComment',
						['news' => $news]);
					$event->setTitle($text);
					$event->setDescription($comment->getFormattedMessage());

					continue;
				}
			}

			$event->setIsOrphaned();
		}
	}
}
