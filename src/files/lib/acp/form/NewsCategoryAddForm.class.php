<?php

namespace cms\acp\form;

use wcf\acp\form\AbstractCategoryAddForm;

/**
 * Shows the news category add form.
 *
 * @author      Jens Krumsieck
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
 */
class NewsCategoryAddForm extends AbstractCategoryAddForm {
	/**
	 * {@inheritdoc}
	 */
	public $activeMenuItem = 'cms.acp.menu.link.cms.news.category.add';

	/**
	 * {@inheritdoc}
	 */
	public $objectTypeName = 'de.codequake.cms.category.news';

	/**
	 * {@inheritdoc}
	 */
	public $pageTitle = 'wcf.category.add';
}
