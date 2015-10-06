<?php

/**
 * @author    Jens Krumsieck
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\system\user\online\location;

use cms\data\category\NewsCategory;
use wcf\data\user\online\UserOnline;
use wcf\system\category\CategoryHandler;
use wcf\system\user\online\location\IUserOnlineLocation;
use wcf\system\WCF;

/**
 * Location implementation for news categories.
 */
class NewsCategoryLocation implements IUserOnlineLocation
{
    /**
     * {@inheritdoc}
     */
    public function cache(UserOnline $user)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function get(UserOnline $user, $languageVariable = '')
    {
        if ($category = CategoryHandler::getInstance()->getCategory($user->objectID)) {
            $category = new NewsCategory($category);

            if ($category->getPermission('canView')) {
                return WCF::getLanguage()->getDynamicVariable($languageVariable, array(
                    'category' => $category,
                ));
            }
        }

        return '';
    }
}
