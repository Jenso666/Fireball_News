<?php

namespace cms\acp\page;

use wcf\acp\page\AbstractCategoryListPage;

/**
 * Shows a list of news categories.
 *
 * @author      Jens Krumsieck
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
 */
class NewsCategoryListPage extends AbstractCategoryListPage {
	/**
	 * {@inheritdoc}
	 */
	public $activeMenuItem = 'cms.acp.menu.link.cms.news.category.list';

	/**
	 * {@inheritdoc}
	 */
	public $objectTypeName = 'de.codequake.cms.category.news';

	/**
	 * {@inheritdoc}
	 */
	public $pageTitle = 'wcf.category.list';
}
