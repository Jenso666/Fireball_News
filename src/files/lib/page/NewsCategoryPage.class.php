<?php

namespace cms\page;

use cms\data\category\NewsCategory;
use cms\data\category\NewsCategoryNodeTree;
use cms\data\news\CategoryNewsList;
use cms\system\counter\VisitCountHandler;
use wcf\page\SortablePage;
use wcf\system\category\CategoryHandler;
use wcf\system\clipboard\ClipboardHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * Page for news of a specific category.
 *
 * @author      Jens Krumsieck, Florian Gail
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
 */
class NewsCategoryPage extends SortablePage {
	/**
	 * @inheritDoc
	 */
	public $itemsPerPage = FIREBALL_NEWS_PER_PAGE;

	/**
	 * @inheritDoc
	 */
	public $validSortFields = [
		'username',
		'newsID',
		'time',
		'subject',
		'clicks',
		'comments',
	];

	/**
	 * @inheritDoc
	 */
	public $defaultSortField = 'time';

	/**
	 * @inheritDoc
	 */
	public $defaultSortOrder = 'DESC';

	/**
	 * id of the category
	 * @var integer
	 */
	public $categoryID = 0;

	/**
	 * category object
	 * @var NewsCategory
	 */
	public $category;

	/**
	 * list of availabla categories
	 * @var NewsCategory[]
	 */
	public $categoryList;

	/**
	 * @inheritDoc
	 */
	public $templateName = 'newsOverview';

	/**
	 * @inheritDoc
	 *
	 * @throws \wcf\system\exception\IllegalLinkException if no id provided with this request or no category with the
	 *                                                    given id exists.
	 * @throws \wcf\system\exception\PermissionDeniedException if category is not accessible for the current user.
	 */
	public function readParameters() {
		parent::readParameters();

		if (isset($_REQUEST['id'])) {
			$this->categoryID = intval($_REQUEST['id']);
		}

		$this->category = CategoryHandler::getInstance()->getCategory($this->categoryID);

		if ($this->category === null) {
			throw new IllegalLinkException();
		}

		$this->category = new NewsCategory($this->category);

		if (!$this->category->isAccessible()) {
			throw new PermissionDeniedException();
		}
	}

	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();

		VisitCountHandler::getInstance()->count();

		// get categories
		$categoryTree = new NewsCategoryNodeTree('de.codequake.cms.category.news');
		$this->categoryList = $categoryTree->getIterator();
		$this->categoryList->setMaxDepth(0);
	}

	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();

		WCF::getTPL()->assign([
			'category' => $this->category,
			'categoryID' => $this->categoryID,
			'controller' => 'NewsList',
			'allowSpidersToIndexThisPage' => true,
			'categoryList' => $this->categoryList,
			'hasMarkedItems' => ClipboardHandler::getInstance()->hasMarkedItems(ClipboardHandler::getInstance()->getObjectTypeID('de.codequake.cms.news'))
		]);
	}

	/**
	 * @inheritDoc
	 */
	public function getObjectType() {
		return 'de.codequake.cms.category.news';
	}

	/**
	 * @inheritDoc
	 */
	public function getObjectID() {
		return $this->categoryID;
	}

	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		if ($this->category) {
			$this->objectList = new CategoryNewsList([$this->category->categoryID]);
		}
		else {
			throw new IllegalLinkException();
		}
	}
}
