<?php

namespace cms\system\dashboard\box;

use cms\data\news\LatestNewsList;
use wcf\data\dashboard\box\DashboardBox;
use wcf\page\IPage;
use wcf\system\dashboard\box\AbstractContentDashboardBox;
use wcf\system\WCF;

/**
 * Dashboard box for latest news
 *
 * @author      Jens Krumsieck
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
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
