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
	 * @inheritDoc
	 */
	protected $langVarPrefix = 'cms.category.news';

	/**
	 * @inheritDoc
	 */
	protected $forceDescription = false;

	/**
	 * @inheritDoc
	 */
	protected $maximumNestingLevel = 1;

	/**
	 * @inheritDoc
	 */
	protected $objectTypes = ['com.woltlab.wcf.acl' => 'de.codequake.cms.category.news'];

	/**
	 * @inheritDoc
	 */
	public function getApplication() {
		return 'cms';
	}

	/**
	 * @inheritDoc
	 */
	public function canAddCategory() {
		return $this->canEditCategory();
	}

	/**
	 * @inheritDoc
	 */
	public function canDeleteCategory() {
		return $this->canEditCategory();
	}

	/**
	 * @inheritDoc
	 */
	public function canEditCategory() {
		return WCF::getSession()->getPermission('admin.fireball.news.canManageCategory');
	}
}
