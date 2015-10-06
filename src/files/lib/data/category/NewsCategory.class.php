<?php

/**
 * @author    Jens Krumsieck
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\data\category;

use wcf\data\category\AbstractDecoratedCategory;
use wcf\system\breadcrumb\Breadcrumb;
use wcf\system\breadcrumb\IBreadcrumbProvider;
use wcf\system\category\CategoryHandler;
use wcf\system\category\CategoryPermissionHandler;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Represents a news category.
 */
class NewsCategory extends AbstractDecoratedCategory implements IBreadcrumbProvider
{
    const OBJECT_TYPE_NAME = 'de.codequake.cms.category.news';

    protected $permissions;

    /**
     * @return bool
     */
    public function isAccessible()
    {
        if ($this->getObjectType()->objectType != self::OBJECT_TYPE_NAME) {
            return false;
        }

        return $this->getPermission('canViewCategory');
    }

    /**
     * @param string $permission
     * @return bool
     */
    public function getPermission($permission)
    {
        if ($this->permissions === null) {
            $this->permissions = CategoryPermissionHandler::getInstance()->getPermissions($this->getDecoratedObject());
        }

        if (array_key_exists($permission, $this->permissions)) {
            return $this->permissions[$permission];
        }

        return (WCF::getSession()->getPermission('user.cms.news.'.$permission) || WCF::getSession()->getPermission('mod.cms.news.'.$permission) || WCF::getSession()->getPermission('admin.cms.news.'.$permission));
    }

    /**
     * @return \wcf\system\breadcrumb\Breadcrumb
     */
    public function getBreadcrumb()
    {
        return new Breadcrumb(WCF::getLanguage()->get($this->title), LinkHandler::getInstance()->getLink('NewsCategory', array(
            'application' => 'cms',
            'object' => $this->getDecoratedObject(),
        )));
    }

    /**
     * @param string[] $permissions
     * @return int[]
     */
    public static function getAccessibleCategoryIDs($permissions = array('canViewCategory'))
    {
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
