<?php

namespace cms\system\comment\manager;

use cms\data\news\News;
use cms\data\news\NewsEditor;
use wcf\system\comment\manager\AbstractCommentManager;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Comment manager for news comments.
 *
 * @author      Jens Krumsieck
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
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

		return ($news->newsID && $news->canRead());
	}

	/**
	 * @inheritDoc
	 */
	public function getLink($objectTypeID, $objectID) {
		return LinkHandler::getInstance()->getLink('News', [
			'application' => 'cms',
			'id' => $objectID,
		]);
	}

	/**
	 * @inheritDoc
	 */
	public function getTitle($objectTypeID, $objectID, $isResponse = false) {
		if ($isResponse) {
			return WCF::getLanguage()->get('cms.news.commentResponse');
		}

		return WCF::getLanguage()->get('cms.news.comment');
	}

	/**
	 * @inheritDoc
	 */
	public function updateCounter($objectID, $value) {
		$news = new News($objectID);
		$editor = new NewsEditor($news);
		$editor->updateCounters(['comments' => $value]);
	}
}
