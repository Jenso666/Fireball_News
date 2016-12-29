<?php

/**
 * @author    Jens Krumsieck
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\data\news;

use wcf\data\IFeedEntry;
use wcf\system\request\LinkHandler;

/**
 * Represents a news as a rss feed entry.
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
		return LinkHandler::getInstance()->getLink('News', array(
			'application' => 'cms',
			'object' => $this->getDecoratedObject(),
			'appendSession' => false,
			'encodeTitle' => true,
		));
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
	public function getExcerpt($maxLength = CMS_NEWS_TRUNCATE_PREVIEW) {
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
		$categoryNames = array();
		foreach ($this->getDecoratedObject()->getCategories() as $category) {
			$categoryNames[] = $category->getTitle();
		}

		return $categoryNames;
	}
}
