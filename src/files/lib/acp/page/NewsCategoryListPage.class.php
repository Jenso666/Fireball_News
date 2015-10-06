<?php

/**
 * @author    Jens Krumsieck
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\acp\page;

use wcf\acp\page\AbstractCategoryListPage;

/**
 * Shows a list of news categories.
 */
class NewsCategoryListPage extends AbstractCategoryListPage
{
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
