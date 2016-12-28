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
	 * {@inheritdoc}
	 */
	public function getTitle() {
		return $this->getDecoratedObject()->getTitle();
	}

	/**
	 * {@inheritdoc}
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
	 * {@inheritdoc}
	 */
	public function getFormattedMessage() {
		return $this->getDecoratedObject()->getFormattedMessage();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getMessage() {
		return $this->getDecoratedObject()->getMessage();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getComments() {
		return $this->comments;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getExcerpt($maxLength = CMS_NEWS_TRUNCATE_PREVIEW) {
		return $this->getDecoratedObject()->getExcerpt($maxLength);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTime() {
		return $this->time;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getUserID() {
		return $this->userID;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getUsername() {
		return $this->username;
	}

	/**
	 * {@inheritdoc}
	 */
	public function __toString() {
		return $this->getDecoratedObject()->__toString();
	}

	/**
	 * {@inheritdoc}
	 */
	public function isVisible() {
		return $this->canRead();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCategories() {
		$categoryNames = array();
		foreach ($this->getDecoratedObject()->getCategories() as $category) {
			$categoryNames[] = $category->getTitle();
		}

		return $categoryNames;
	}
}
