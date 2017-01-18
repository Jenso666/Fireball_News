<?php

/**
 * @author    Jens Krumsieck
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
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

	public function getLink() {
		return LinkHandler::getInstance()->getLink('NewsCategory', [
			'application' => 'cms',
			'object' => $this
		]);
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
	public static function getAccessibleCategoryIDs($permissions = array('canViewCategory')) {
		$categoryIDs = array();
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
