<?php

namespace cms\page;

use wcf\page\SortablePage;
use wcf\system\clipboard\ClipboardHandler;
use wcf\system\WCF;

/**
 * Page for the news archive.
 *
 * @author      Jens Krumsieck
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
 */
class NewsArchivePage extends SortablePage {
	/**
	 * {@inheritdoc}
	 */
	public $activeMenuItem = 'cms.page.news.archive';

	/**
	 * {@inheritdoc}
	 */
	public $enableTracking = true;

	/**
	 * {@inheritdoc}
	 */
	public $itemsPerPage = FIREBALL_NEWS_PER_PAGE;

	public $limit = 10;

	public $categoryList;

	/**
	 * {@inheritdoc}
	 */
	public $defaultSortField = 'time';

	/**
	 * {@inheritdoc}
	 */
	public $defaultSortOrder = 'DESC';

	/**
	 * {@inheritdoc}
	 */
	public $objectListClassName = 'cms\data\news\AccessibleNewsList';

	/**
	 * {@inheritdoc}
	 */
	public $validSortFields = array(
		'subject',
		'time',
		'clicks'
	);

	/**
	 * {@inheritdoc}
	 */
	public function assignVariables() {
		parent::assignVariables();

		WCF::getTPL()->assign(array(
			'allowSpidersToIndexThisPage' => true,
			'hasMarkedItems' => ClipboardHandler::getInstance()->hasMarkedItems(ClipboardHandler::getInstance()->getObjectTypeID('de.codequake.cms.news'))
		));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getObjectType() {
		return 'de.codequake.cms.news';
	}
}
