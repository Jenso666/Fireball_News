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
	 * {@inheritdoc}
	 */
	protected $permissionAdd = 'user.fireball.news.canAddComment';

	/**
	 * {@inheritdoc}
	 */
	protected $permissionCanModerate = 'mod.fireball.news.canModerateComment';

	/**
	 * {@inheritdoc}
	 */
	protected $permissionDelete = 'user.fireball.news.canDeleteComment';

	/**
	 * {@inheritdoc}
	 */
	protected $permissionEdit = 'user.fireball.news.canEditComment';

	/**
	 * {@inheritdoc}
	 */
	protected $permissionModDelete = 'mod.fireball.news.canDeleteComment';

	/**
	 * {@inheritdoc}
	 */
	protected $permissionModEdit = 'mod.fireball.news.canEditComment';

	/**
	 * {@inheritdoc}
	 */
	public function isAccessible($objectID, $validateWritePermission = false) {
		$news = new News($objectID);

		return ($news->newsID && $news->canRead());
	}

	/**
	 * {@inheritdoc}
	 */
	public function getLink($objectTypeID, $objectID) {
		return LinkHandler::getInstance()->getLink('News', array(
			'application' => 'cms',
			'id' => $objectID,
		));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTitle($objectTypeID, $objectID, $isResponse = false) {
		if ($isResponse) {
			return WCF::getLanguage()->get('cms.news.commentResponse');
		}

		return WCF::getLanguage()->getDynamicVariable('cms.news.comment');
	}

	/**
	 * {@inheritdoc}
	 */
	public function updateCounter($objectID, $value) {
		$news = new News($objectID);
		$editor = new NewsEditor($news);
		$editor->updateCounters(array('comments' => $value));
	}
}
