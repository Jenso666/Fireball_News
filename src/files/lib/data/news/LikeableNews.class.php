<?php

/**
 * @author    Jens Krumsieck
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\data\news;

use wcf\data\like\object\AbstractLikeObject;
use wcf\system\request\LinkHandler;

/**
 * Represents a likeable news.
 */
class LikeableNews extends AbstractLikeObject {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = News::class;

	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return $this->subject;
	}

	/**
	 * @inheritDoc
	 */
	public function getURL() {
		return LinkHandler::getInstance()->getLink('News', array(
			'application' => 'cms',
			'object' => $this->getDecoratedObject(),
		));
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
	public function getObjectID() {
		return $this->newsID;
	}

	/**
	 * @inheritDoc
	 */
	public function updateLikeCounter($cumulativeLikes) {
		// update cumulative likes
		$editor = new NewsEditor($this->getDecoratedObject());
		$editor->update(array('cumulativeLikes' => $cumulativeLikes,));
	}
}
