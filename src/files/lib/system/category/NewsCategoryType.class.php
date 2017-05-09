<?php

namespace cms\system\category;

use wcf\system\category\AbstractCategoryType;
use wcf\system\WCF;

/**
 * Category type for news categories
 *
 * @author      Jens Krumsieck
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
 */
class NewsCategoryType extends AbstractCategoryType {
	/**
	 * {@inheritdoc}
	 */
	protected $langVarPrefix = 'cms.category.news';

	/**
	 * {@inheritdoc}
	 */
	protected $forceDescription = false;

	/**
	 * {@inheritdoc}
	 */
	protected $maximumNestingLevel = 1;

	/**
	 * {@inheritdoc}
	 */
	protected $objectTypes = array('com.woltlab.wcf.acl' => 'de.codequake.cms.category.news',);

	/**
	 * {@inheritdoc}
	 */
	public function getApplication() {
		return 'cms';
	}

	/**
	 * {@inheritdoc}
	 */
	public function canAddCategory() {
		return $this->canEditCategory();
	}

	/**
	 * {@inheritdoc}
	 */
	public function canDeleteCategory() {
		return $this->canEditCategory();
	}

	/**
	 * {@inheritdoc}
	 */
	public function canEditCategory() {
		return WCF::getSession()->getPermission('admin.fireball.news.canManageCategory');
	}
}
