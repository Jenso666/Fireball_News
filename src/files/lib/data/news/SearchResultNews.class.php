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
	 * {@inheritdoc}
	 */
	public function getFormattedMessage() {
		return SearchResultTextParser::getInstance()->parse($this->getDecoratedObject()->getExcerpt());
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSubject() {
		return $this->subject;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getLink($query = '') {
		if ($query) {
			return LinkHandler::getInstance()->getLink('News', array('application' => 'cms', 'object' => $this->getDecoratedObject(), 'highlight' => urlencode($query),));
		}

		return $this->getDecoratedObject()->getLink();
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
	public function getObjectTypeName() {
		return 'de.codequake.cms.news';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getContainerTitle() {
		return '';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getContainerLink() {
		return '';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getUserProfile() {
		if ($this->userProfile === null) {
			$this->userProfile = new UserProfile(new User($this->userID));
		}

		return $this->userProfile;
	}
}
