<?php

namespace cms\data\news;

use wcf\data\IFeedEntry;
use wcf\system\request\LinkHandler;

/**
 * Represents a news as a rss feed entry.
 *
 * @author      Jens Krumsieck
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
 */
class NewsFeed extends ViewableNews implements IFeedEntry {
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return $this->getDecoratedObject()->getTitle();
	}

	/**
	 * @inheritDoc
	 */
	public function getLink() {
		return LinkHandler::getInstance()->getLink('News', [
			'application' => 'cms',
			'object' => $this->getDecoratedObject(),
			'appendSession' => false,
			'encodeTitle' => true,
		]);
	}

	/**
	 * @inheritDoc
	 */
	public function getFormattedMessage() {
		return $this->getDecoratedObject()->getFormattedMessage();
	}

	/**
	 * @inheritDoc
	 */
	public function getMessage() {
		return $this->getDecoratedObject()->getMessage();
	}

	/**
	 * @inheritDoc
	 */
	public function getComments() {
		return $this->comments;
	}

	/**
	 * @inheritDoc
	 */
	public function getExcerpt($maxLength = FIREBALL_NEWS_TRUNCATE_PREVIEW) {
		return $this->getDecoratedObject()->getExcerpt($maxLength);
	}

	/**
	 * @inheritDoc
	 */
	public function getTime() {
		return $this->time;
	}

	/**
	 * @inheritDoc
	 */
	public function getUserID() {
		return $this->userID;
	}

	/**
	 * @inheritDoc
	 */
	public function getUsername() {
		return $this->username;
	}

	/**
	 * @inheritDoc
	 */
	/** @noinspection PhpToStringReturnInspection */
	public function __toString() {
		return $this->getDecoratedObject()->__toString();
	}

	/**
	 * @inheritDoc
	 */
	public function isVisible() {
		return $this->canRead();
	}

	/**
	 * @inheritDoc
	 */
	public function getCategories() {
		$categoryNames = [];
		foreach ($this->getDecoratedObject()->getCategories() as $category) {
			$categoryNames[] = $category->getTitle();
		}

		return $categoryNames;
	}
}
