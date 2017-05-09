<?php

namespace cms\data\news;

use wcf\data\like\object\AbstractLikeObject;
use wcf\system\request\LinkHandler;

/**
 * Represents a likeable news
 *
 * @author      Jens Krumsieck, Florian Gail
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
 */
class LikeableNews extends AbstractLikeObject {
	/**
	 * {@inheritdoc}
	 */
	protected static $baseClass = 'cms\data\news\News';

	/**
	 * {@inheritdoc}
	 */
	public function getTitle() {
		return $this->subject;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getURL() {
		return LinkHandler::getInstance()->getLink('News', array(
			'application' => 'cms',
			'object' => $this->getDecoratedObject(),
		));
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
	public function getObjectID() {
		return $this->newsID;
	}

	/**
	 * {@inheritdoc}
	 */
	public function updateLikeCounter($cumulativeLikes) {
		// update cumulative likes
		$editor = new NewsEditor($this->getDecoratedObject());
		$editor->update(array('cumulativeLikes' => $cumulativeLikes));
	}
}
