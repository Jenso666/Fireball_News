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
class NewsContentType extends AbstractContentType
{
    /**
     * {@inheritdoc}
     */
    protected $icon = 'icon-archive';

    /**
     * {@inheritdoc}
     */
    public $objectType = 'de.codequake.cms.content.type.news';

    /**
     * {@inheritdoc}
     */
    public function validate($data)
    {
        if (empty($data['categoryIDs'])) {
            throw new UserInputException('categoryIDs', 'empty');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getFormTemplate()
    {
        $excludedCategoryIDs = array_diff(NewsCategory::getAccessibleCategoryIDs(), NewsCategory::getAccessibleCategoryIDs(array(
            'canAddNews',
        )));
        $categoryTree = new NewsCategoryNodeTree('de.codequake.cms.category.news', 0, false, $excludedCategoryIDs);
        $categoryList = $categoryTree->getIterator();
        $categoryList->setMaxDepth(0);
        WCF::getTPL()->assign('categoryList', $categoryList);

        return 'newsContentType';
    }

    /**
     * {@inheritdoc}
     */
    public function getOutput(Content $content)
    {
        $type = ($content->type != '') ? $content->type : 'standard';

        $newsList = new CategoryNewsList($content->categoryIDs);
        $newsList->sqlLimit = $content->limit;
        $newsList->readObjects();
        $newsList = $newsList->getObjects();

        WCF::getTPL()->assign(array(
            'objects' => $newsList,
            'type' => $type,
        ));

        return WCF::getTPL()->fetch('newsContentTypeOutput', 'cms');
    }
}
