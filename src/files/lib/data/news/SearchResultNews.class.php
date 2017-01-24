<?php

/**
 * @author    Jens Krumsieck
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\data\news;

use wcf\data\search\ISearchResultObject;
use wcf\data\user\User;
use wcf\data\user\UserProfile;
use wcf\system\request\LinkHandler;
use wcf\system\search\SearchResultTextParser;

/**
 * Represents a news as search result.
 */
class SearchResultNews extends ViewableNews implements ISearchResultObject {
	/**
	 * @inheritDoc
	 */
	public function getFormattedMessage() {
		return SearchResultTextParser::getInstance()->parse($this->getDecoratedObject()->getExcerpt());
	}

	/**
	 * @inheritDoc
	 */
	public function getSubject() {
		return $this->subject;
	}

	/**
	 * @inheritDoc
	 */
	public function getLink($query = '') {
		if ($query) {
			return LinkHandler::getInstance()->getLink('News', [
				'application' => 'cms',
				'object' => $this->getDecoratedObject(),
				'highlight' => urlencode($query),
			]);
		}

		return $this->getDecoratedObject()->getLink();
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
	public function getObjectTypeName() {
		return 'de.codequake.cms.news';
	}

	/**
	 * @inheritDoc
	 */
	public function getContainerTitle() {
		return '';
	}

	/**
	 * @inheritDoc
	 */
	public function getContainerLink() {
		return '';
	}

	/**
	 * @inheritDoc
	 */
	public function getUserProfile() {
		if ($this->userProfile === null) {
			$this->userProfile = new UserProfile(new User($this->userID));
		}

		return $this->userProfile;
	}
}
