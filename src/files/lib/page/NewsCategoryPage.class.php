<?php

/**
 * @author    Jens Krumsieck
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\page;

use cms\data\category\NewsCategory;
use cms\data\category\NewsCategoryNodeTree;
use cms\data\news\CategoryNewsList;
use cms\system\counter\VisitCountHandler;
use wcf\page\SortablePage;
use wcf\system\breadcrumb\Breadcrumb;
use wcf\system\category\CategoryHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Page for news of a specific category.
 */
class NewsCategoryPage extends SortablePage {
	/**
	 * @inheritDoc
	 */
	public $itemsPerPage = CMS_NEWS_PER_PAGE;

	/**
	 * @inheritDoc
	 */
	public $validSortFields = array(
		'username',
		'newsID',
		'time',
		'subject',
		'clicks',
		'comments',
	);

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
		WCF::getBreadcrumbs()->add(new Breadcrumb(WCF::getLanguage()->get('cms.page.news'),
			LinkHandler::getInstance()->getLink('NewsOverview', array('application' => 'cms',))));

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

		WCF::getTPL()->assign(array(
			'category' => $this->category,
			'categoryID' => $this->categoryID,
			'controller' => 'NewsList',
			'allowSpidersToIndexThisPage' => true,
			'categoryList' => $this->categoryList,
		));
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
			$this->objectList = new CategoryNewsList(array($this->category->categoryID));
		}
		else {
			throw new IllegalLinkException();
		}
	}
}
