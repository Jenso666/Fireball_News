<?php

namespace cms\data\category;

use wcf\data\category\AbstractDecoratedCategory;
use wcf\data\user\User;
use wcf\data\ITitledLinkObject;
use wcf\system\category\CategoryHandler;
use wcf\system\category\CategoryPermissionHandler;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Represents a news category.
 *
 * @author      Jens Krumsieck, Florian Gail
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
 */
class NewsCategory extends AbstractDecoratedCategory implements ITitledLinkObject {
	const OBJECT_TYPE_NAME = 'de.codequake.cms.category.news';

	protected $permissions;

	/**
	 * @return boolean
	 * @throws \wcf\system\exception\SystemException
	 */
	public function isAccessible() {
		if ($this->getObjectType()->objectType != self::OBJECT_TYPE_NAME) {
			return false;
		}

		return $this->getPermission('canViewCategory');
	}

	/**
	 * @inheritDoc
	 */
	public function getLink() {
		return LinkHandler::getInstance()->getLink('NewsCategory', [
			'application' => 'cms',
			'object' => $this
		]);
	}

	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return WCF::getLanguage()->get($this->title);
	}

	/**
	 * @param string $permission
	 * @param User   $user
	 * @return bool
	 * @throws \wcf\system\exception\SystemException
	 */
	public function getPermission($permission, User $user = null) {
		if ($this->permissions === null) {
			$this->permissions = CategoryPermissionHandler::getInstance()->getPermissions($this->getDecoratedObject());
		}

		if (array_key_exists($permission, $this->permissions)) {
			return $this->permissions[$permission];
		}

		return (WCF::getSession()->getPermission('user.fireball.news.' . $permission) || WCF::getSession()->getPermission('mod.fireball.news.' . $permission) || WCF::getSession()->getPermission('admin.fireball.news.' . $permission));
	}

	/**
	 * @param string[] $permissions
	 * @return \integer[]
	 * @throws \wcf\system\exception\SystemException
	 */
	public static function getAccessibleCategoryIDs($permissions = ['canViewCategory']) {
		$categoryIDs = [];
		foreach (CategoryHandler::getInstance()->getCategories(self::OBJECT_TYPE_NAME) as $category) {
			$result = true;
			$category = new self($category);
			foreach ($permissions as $permission) {
				$result = $result && $category->getPermission($permission);
			}

			if ($result) {
				$categoryIDs[] = $category->categoryID;
			}
		}

		return $categoryIDs;
	}
}
