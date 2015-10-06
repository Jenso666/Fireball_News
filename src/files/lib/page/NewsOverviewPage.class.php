<?php

/**
 * @author    Jens Krumsieck
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\page;

use cms\data\category\NewsCategoryNodeTree;
use wcf\page\SortablePage;
use wcf\system\dashboard\DashboardHandler;
use wcf\system\menu\page\PageMenu;
use wcf\system\request\LinkHandler;
use wcf\system\user\collapsible\content\UserCollapsibleContentHandler;
use wcf\system\MetaTagHandler;
use wcf\system\WCF;

/**
 * Page for the news category list.
 */
class NewsOverviewPage extends SortablePage
{
    /**
     * {@inheritdoc}
     */
    public $activeMenuItem = 'cms.page.news';

    /**
     * {@inheritdoc}
     */
    public $enableTracking = true;

    /**
     * {@inheritdoc}
     */
    public $itemsPerPage = CMS_NEWS_PER_PAGE;

    /**
     * {@inheritdoc}
     */
    public $objectListClassName = 'cms\data\news\AccessibleNewsList';

    public $limit = 10;

    public $categoryList;

    /**
     * {@inheritdoc}
     */
    public function readData()
    {
        parent::readData();

        $categoryTree = new NewsCategoryNodeTree('de.codequake.cms.category.news');
        $this->categoryList = $categoryTree->getIterator();
        $this->categoryList->setMaxDepth(0);

        if (PageMenu::getInstance()->getLandingPage()->menuItem == 'cms.page.news') {
            WCF::getBreadcrumbs()->remove(0);

            MetaTagHandler::getInstance()->addTag('og:url', 'og:url', LinkHandler::getInstance()->getLink('NewsList', array(
                'application' => 'cms',
            )), true);
            MetaTagHandler::getInstance()->addTag('og:type', 'og:type', 'website', true);
            MetaTagHandler::getInstance()->addTag('og:title', 'og:title', WCF::getLanguage()->get(PAGE_TITLE), true);
            MetaTagHandler::getInstance()->addTag('og:description', 'og:description', WCF::getLanguage()->get(PAGE_DESCRIPTION), true);
            MetaTagHandler::getInstance()->addTag('generator', 'generator', 'Fireball CMS');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function assignVariables()
    {
        parent::assignVariables();

        DashboardHandler::getInstance()->loadBoxes('de.codequake.cms.news.newsList', $this);

        WCF::getTPL()->assign(array(
            'categoryList' => $this->categoryList,
            'allowSpidersToIndexThisPage' => true,
            'sidebarCollapsed' => UserCollapsibleContentHandler::getInstance()->isCollapsed('com.woltlab.wcf.collapsibleSidebar', 'de.codequake.cms.news.newsList'),
            'sidebarName' => 'de.codequake.cms.news.newsList',
        ));
    }
}
