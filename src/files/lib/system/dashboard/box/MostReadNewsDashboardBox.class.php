<?php

/**
 * @author    Jens Krumsieck
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\system\dashboard\box;

use cms\data\news\MostReadNewsList;
use wcf\data\dashboard\box\DashboardBox;
use wcf\page\IPage;
use wcf\system\dashboard\box\AbstractSidebarDashboardBox;
use wcf\system\WCF;

/**
 * Dashboard box for most read news.
 */
class MostReadNewsDashboardBox extends AbstractSidebarDashboardBox
{
    public $mostReadNews;

    /**
     * {@inheritdoc}
     */
    public function init(DashboardBox $box, IPage $page)
    {
        parent::init($box, $page);

        $this->mostReadNews = new MostReadNewsList();
        $this->mostReadNews->sqlLimit = CMS_NEWS_LATEST_LIMIT;
        $this->mostReadNews->readObjects();
    }

    /**
     * {@inheritdoc}
     */
    protected function render()
    {
        if (0 === count($this->mostReadNews)) {
            return '';
        }

        WCF::getTPL()->assign('mostReadNews', $this->mostReadNews);

        return WCF::getTPL()->fetch('dashboardBoxMostReadNews', 'cms');
    }
}
