<?php

/**
 * @author    Jens Krumsieck
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\data\news;

use cms\data\category\NewsCategory;
use wcf\system\category\CategoryHandler;
use wcf\system\WCF;

/**
 * Represents a list of news in a specific category.
 */
class CategoryNewsList extends AccessibleNewsList
{
    /**
     * {@inheritdoc}
     *
     * @param int[] $categoryIDs
     */
    public function __construct(array $categoryIDs)
    {
        parent::__construct();

        if (0 !== count($categoryIDs)) {
            $this->getConditionBuilder()->add('news_to_category.categoryID IN (?)', array($categoryIDs));
            $this->getConditionBuilder()->add('news.newsID = news_to_category.newsID');
        } else {
            $this->getConditionBuilder()->add('1=0');
        }

        foreach ($categoryIDs as $categoryID) {
            $category = new NewsCategory(CategoryHandler::getInstance()->getCategory($categoryID));

            if (!$category->getPermission('canViewDelayedNews')) {
                $this->getConditionBuilder()->add('news.isDisabled = 0');
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function readObjectIDs()
    {
        $this->objectIDs = array();

        $sql = '
            SELECT DISTINCT(news_to_category.newsID) AS objectID
            FROM cms'.WCF_N.'_news_to_category news_to_category, cms'.WCF_N.'_news news
            '.$this->sqlConditionJoins.'
            '.$this->getConditionBuilder().'
            '.(!empty($this->sqlOrderBy) ? 'ORDER BY '.$this->sqlOrderBy : '');
        $statement = WCF::getDB()->prepareStatement($sql, $this->sqlLimit, $this->sqlOffset);
        $statement->execute($this->getConditionBuilder()->getParameters());

        while ($row = $statement->fetchArray()) {
            $this->objectIDs[] = $row['objectID'];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function countObjects()
    {
        $sql = '
            SELECT COUNT(*) AS count
            FROM cms'.WCF_N.'_news_to_category news_to_category, cms'.WCF_N.'_news news
            '.$this->sqlConditionJoins.'
            '.$this->getConditionBuilder();
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute($this->getConditionBuilder()->getParameters());
        $row = $statement->fetchArray();

        return $row['count'];
    }
}
