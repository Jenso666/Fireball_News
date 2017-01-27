<?php

/**
 * @author    Jens Krumsieck, Florian Frantzen
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\system\user\activity\event;

use cms\data\news\NewsList;
use wcf\data\comment\response\CommentResponseList;
use wcf\data\comment\CommentList;
use wcf\data\user\User;
use wcf\system\user\activity\event\IUserActivityEvent;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Activity event for news comment responses.
 */
class NewsCommentResponseUserActivityEvent extends SingletonFactory implements IUserActivityEvent {
	/**
	 * {@inheritdoc}
	 */
	public function prepare(array $events) {
		$commentIDs = $responseIDs = $newsIDs = array();

		foreach ($events as $event) {
			$responseIDs[] = $event->objectID;
		}

		$responseList = new CommentResponseList();
		$responseList->setObjectIDs($responseIDs);
		$responseList->readObjects();
		$responses = $responseList->getObjects();

		foreach ($responses as $response) {
			$commentIDs[] = $response->commentID;
		}

		$commentList = new CommentList();
		$commentList->setObjectIDs($commentIDs);
		$commentList->readObjects();
		$comments = $commentList->getObjects();

		// get news
		foreach ($comments as $comment) {
			$newsIDs[] = $comment->objectID;
		}

		$newsList = new NewsList();
		$newsList->setObjectIDs($newsIDs);
		$newsList->readObjects();
		$newsEntries = $newsList->getObjects();

		/** @var \wcf\data\user\activity\event\ViewableUserActivityEvent $event */
		foreach ($events as $event) {
			if (array_key_exists($event->objectID, $responses)) {
				/** @var \wcf\data\comment\response\CommentResponse $response */
				$response = $responses[$event->objectID];

				if (array_key_exists($response->commentID, $comments)) {
					/** @var \wcf\data\comment\Comment $comment */
					$comment = $comments[$response->commentID];

					if (array_key_exists($comment->objectID, $newsEntries)) {
						/** @var \cms\data\news\News $news */
						$news = $newsEntries[$comment->objectID];

						if (!$news->canRead()) {
							continue;
						}

						$event->setIsAccessible();

						$text = WCF::getLanguage()->getDynamicVariable('wcf.user.profile.recentActivity.newsCommentResponse',
							array(
								'author' => new User($comment->userID),
								'news' => $newsEntries[$comment->objectID],
							));
						$event->setTitle($text);
						$event->setDescription($response->getFormattedMessage());

						continue;
					}
				}
			}

			$event->setIsOrphaned();
		}
	}
}
