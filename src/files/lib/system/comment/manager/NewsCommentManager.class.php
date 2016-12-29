<?php

/**
 * @author    Jens Krumsieck
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\system\comment\manager;

use cms\data\news\News;
use cms\data\news\NewsEditor;
use wcf\system\comment\manager\AbstractCommentManager;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Comment manager for news comments.
 */
class NewsCommentManager extends AbstractCommentManager {
	/**
	 * @inheritDoc
	 */
	protected $permissionAdd = 'user.fireball.news.canAddComment';

	/**
	 * @inheritDoc
	 */
	protected $permissionCanModerate = 'mod.fireball.news.canModerateComment';

	/**
	 * @inheritDoc
	 */
	protected $permissionDelete = 'user.fireball.news.canDeleteComment';

	/**
	 * @inheritDoc
	 */
	protected $permissionEdit = 'user.fireball.news.canEditComment';

	/**
	 * @inheritDoc
	 */
	protected $permissionModDelete = 'mod.fireball.news.canDeleteComment';

	/**
	 * @inheritDoc
	 */
	protected $permissionModEdit = 'mod.fireball.news.canEditComment';

	/**
	 * @inheritDoc
	 */
	public function isAccessible($objectID, $validateWritePermission = false) {
		$news = new News($objectID);

		return ($news->newsID && !$news->canRead());
	}

	/**
	 * @inheritDoc
	 */
	public function getLink($objectTypeID, $objectID) {
		return LinkHandler::getInstance()->getLink('News', array(
			'application' => 'cms',
			'id' => $objectID,
		));
	}

	/**
	 * @inheritDoc
	 */
	public function getTitle($objectTypeID, $objectID, $isResponse = false) {
		if ($isResponse) {
			return WCF::getLanguage()->get('cms.news.commentResponse');
		}

		return WCF::getLanguage()->getDynamicVariable('cms.news.comment');
	}

	/**
	 * @inheritDoc
	 */
	public function updateCounter($objectID, $value) {
		$news = new News($objectID);
		$editor = new NewsEditor($news);
		$editor->updateCounters(array('comments' => $value,));
	}
}
