<?php

/**
 * @author    Jens Krumsieck, Florian Gail
 * @copyright 2014-2015 codeQuake
 * @license   LGPL
 */
namespace cms\system\dashboard\box;

use cms\data\news\LatestNewsList;
use wcf\data\dashboard\box\DashboardBox;
use wcf\page\IPage;
use wcf\system\dashboard\box\AbstractContentDashboardBox;
use wcf\system\WCF;

/**
 * Dashboard box for latest news.
 */
class LatestNewsContentDashboardBox extends AbstractContentDashboardBox {
	public $latestNews;

	/**
	 * {@inheritdoc}
	 */
	public function init(DashboardBox $box, IPage $page) {
		parent::init($box, $page);

		$this->latestNews = new LatestNewsList();
		$this->latestNews->sqlLimit = FIREBALL_NEWS_LATEST_LIMIT;
		$this->latestNews->readObjects();
	}

	/**
	 * {@inheritdoc}
	 */
	protected function render() {
		if (!count($this->latestNews)) {
			return '';
		}

		WCF::getTPL()->assign(array('latestNews' => $this->latestNews,));

		return WCF::getTPL()->fetch('dashboardBoxLatestNewsContent', 'cms');
	}
}
