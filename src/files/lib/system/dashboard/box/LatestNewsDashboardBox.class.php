<?php

/**
 * @author    Jens Krumsieck
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\system\dashboard\box;

use cms\data\news\LatestNewsList;
use wcf\data\dashboard\box\DashboardBox;
use wcf\page\IPage;
use wcf\system\dashboard\box\AbstractSidebarDashboardBox;
use wcf\system\WCF;

/**
 * Dashboard box for latest news.
 */
class LatestNewsDashboardBox extends AbstractSidebarDashboardBox
{
    public $latestNews;

    /**
     * {@inheritdoc}
     */
    public function init(DashboardBox $box, IPage $page)
    {
        parent::init($box, $page);

        $this->latestNews = new LatestNewsList();
        $this->latestNews->sqlLimit = CMS_NEWS_LATEST_LIMIT;
        $this->latestNews->readObjects();
    }

    /**
     * {@inheritdoc}
     */
    protected function render()
    {
        if (0 === count($this->latestNews)) {
            return '';
        }

        WCF::getTPL()->assign('latestNews', $this->latestNews);

        return WCF::getTPL()->fetch('dashboardBoxLatestNews', 'cms');
    }
}
