<?php

/**
 * @author    Jens Krumsieck
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\system\content\type;

use cms\data\category\NewsCategory;
use cms\data\category\NewsCategoryNodeTree;
use cms\data\content\Content;
use cms\data\news\CategoryNewsList;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Content type to display news of a specific category.
 */
class NewsContentType extends AbstractContentType {
	/**
	 * @inheritDoc
	 */
	protected $icon = 'icon-archive';

	/**
	 * @inheritDoc
	 */
	public $objectType = 'de.codequake.cms.content.type.news';

	/**
	 * @inheritDoc
	 */
	public function validate($data) {
		if (empty($data['categoryIDs'])) {
			throw new UserInputException('categoryIDs', 'empty');
		}
	}

	/**
	 * @inheritDoc
	 */
	public function getFormTemplate() {
		$excludedCategoryIDs = array_diff(NewsCategory::getAccessibleCategoryIDs(),
			NewsCategory::getAccessibleCategoryIDs(['canAddNews',]));
		$categoryTree = new NewsCategoryNodeTree('de.codequake.cms.category.news', 0, false, $excludedCategoryIDs);
		$categoryList = $categoryTree->getIterator();
		$categoryList->setMaxDepth(0);
		WCF::getTPL()->assign('categoryList', $categoryList);

		return 'newsContentType';
	}

	/**
	 * @inheritDoc
	 */
	public function getOutput(Content $content) {
		$type = ($content->type != '') ? $content->type : 'standard';

		$newsList = new CategoryNewsList($content->categoryIDs);
		$newsList->sqlLimit = $content->limit;
		$newsList->readObjects();
		$newsList = $newsList->getObjects();

		WCF::getTPL()->assign([
			'objects' => $newsList,
			'type' => $type,
		]);

		return WCF::getTPL()->fetch('newsContentTypeOutput', 'cms');
	}
}
