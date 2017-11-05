<?php

namespace cms\page;

use cms\data\category\NewsCategoryNodeTree;
use cms\data\news\AccessibleNewsList;
use wcf\page\SortablePage;
use wcf\system\clipboard\ClipboardHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Page for the news category list.
 *
 * @author      Jens Krumsieck
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
 */
class NewsOverviewPage extends SortablePage {
	/**
	 * @inheritDoc
	 */
	public $itemsPerPage = FIREBALL_NEWS_PER_PAGE;

	/**
	 * @inheritDoc
	 */
	public $objectListClassName = AccessibleNewsList::class;

	/**
	 * list of available categories
	 * @var \cms\data\category\NewsCategory[]
	 */
	public $categoryList;
	
	/**
	 * username filter
	 * @var string
	 */
	public $username = '';
	
	/**
	 * day filter (start)
	 * @var integer|\DateTime
	 */
	public $dateStart = 0;
	
	/**
	 * day filter (end)
	 * @var integer
	 */
	public $dateEnd = 0;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (!empty($_REQUEST['username'])) $this->username = StringUtil::trim($_REQUEST['username']);
		if (!empty($_REQUEST['time'])) $this->dateStart = \DateTime::createFromFormat('Y-m-d', $_REQUEST['time'], WCF::getUser()->getTimeZone());
		if (is_bool($this->dateStart) || is_numeric($this->dateStart)) {
			$this->dateStart = 0;
		} else {
			$this->dateStart->setTime(0, 0, 0, 0);
			$this->dateEnd = $this->dateStart->getTimestamp() + 86400;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();

		$categoryTree = new NewsCategoryNodeTree('de.codequake.cms.category.news');
		$this->categoryList = $categoryTree->getIterator();
		$this->categoryList->setMaxDepth(0);
	}
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		if (!empty($this->username)) {
			$this->objectList->getConditionBuilder()->add('news.username = ?', [$this->username]);
		}
		
		if (!empty($this->dateStart)) {
			$this->objectList->getConditionBuilder()->add('news.time >= ?', [$this->dateStart->getTimestamp()]);
			$this->objectList->getConditionBuilder()->add('news.time <= ?', [$this->dateEnd]);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();

		WCF::getTPL()->assign([
			'username' => $this->username,
			'time' => is_integer($this->dateStart) ? $this->dateStart : $this->dateStart->format('Y-m-d'),
			
			'categoryList' => $this->categoryList,
			'allowSpidersToIndexThisPage' => true,
			'hasMarkedItems' => ClipboardHandler::getInstance()->hasMarkedItems(ClipboardHandler::getInstance()->getObjectTypeID('de.codequake.cms.news'))
		]);
	}
}
