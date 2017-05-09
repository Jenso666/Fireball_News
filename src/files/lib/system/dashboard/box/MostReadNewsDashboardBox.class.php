<?php

namespace cms\system\dashboard\box;

use cms\data\news\MostReadNewsList;
use wcf\data\dashboard\box\DashboardBox;
use wcf\page\IPage;
use wcf\system\dashboard\box\AbstractSidebarDashboardBox;
use wcf\system\WCF;

/**
 * Dashboard box for most read news.
 *
 * @author      Jens Krumsieck
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
 */
class MostReadNewsDashboardBox extends AbstractSidebarDashboardBox {
	public $mostReadNews;

	/**
	 * {@inheritdoc}
	 */
	public function init(DashboardBox $box, IPage $page) {
		parent::init($box, $page);

		$this->mostReadNews = new MostReadNewsList();
		$this->mostReadNews->sqlLimit = FIREBALL_NEWS_LATEST_LIMIT;
		$this->mostReadNews->readObjects();
	}

	/**
	 * {@inheritdoc}
	 */
	protected function render() {
		if (0 === count($this->mostReadNews)) {
			return '';
		}

		WCF::getTPL()->assign('mostReadNews', $this->mostReadNews);

		return WCF::getTPL()->fetch('dashboardBoxMostReadNews', 'cms');
	}
}
