<?php

namespace cms\system\label\object\type;

use cms\data\category\NewsCategoryNodeTree;
use wcf\data\category\CategoryEditor;
use wcf\system\label\object\type\AbstractLabelObjectTypeHandler;
use wcf\system\label\object\type\LabelObjectType;
use wcf\system\label\object\type\LabelObjectTypeContainer;

/**
 * @author      Florian Gail
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
 */
class NewsCategoryLabelObjectTypeHandler extends AbstractLabelObjectTypeHandler {
	/**
	 * @var        \cms\data\category\NewsCategoryNodeTree
	 */
	public $categoryNodeTree;
	
	/**
	 * @inheritDoc
	 */
	public $objectTypeID = 0;
	
	/**
	 * @inheritDoc
	 */
	protected function init() {
		$this->categoryNodeTree = new NewsCategoryNodeTree('de.codequake.cms.category.news');
	}
	
	/**
	 * @inheritDoc
	 */
	public function setObjectTypeID($objectTypeID) {
		parent::setObjectTypeID($objectTypeID);
		
		// build label object type container
		$this->container = new LabelObjectTypeContainer($this->objectTypeID);
		
		foreach ($this->categoryNodeTree as $node) {
			$objectType = new LabelObjectType($node->getDecoratedObject()->getTitle(), $node->getDecoratedObject()->categoryID, $node->getDepth() - 1);
			$this->container->add($objectType);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		CategoryEditor::resetCache();
	}
}
