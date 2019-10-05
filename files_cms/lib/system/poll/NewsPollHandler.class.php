<?php

namespace cms\system\poll;

use cms\data\news\News;
use wcf\data\poll\Poll;
use wcf\system\poll\AbstractPollHandler;
use wcf\system\WCF;

/**
 * Poll handler for news.
 *
 * @author      Jens Krumsieck
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
 */
class NewsPollHandler extends AbstractPollHandler {
	/**
	 * @inheritDoc
	 */
	public function canStartPublicPoll() {
		return (WCF::getSession()->getPermission('user.fireball.news.canStartPublicPoll') ? true : false);
	}

	/**
	 * @inheritDoc
	 */
	public function canVote() {
		return (WCF::getSession()->getPermission('user.fireball.news.canVotePoll') ? true : false);
	}

	/**
	 * @inheritDoc
	 */
	public function getRelatedObject(Poll $poll) {
		$news = new News($poll->objectID);
		if ($news->newsID && $news->pollID == $poll->pollID) {
			return $news;
		}

		return null;
	}
}
