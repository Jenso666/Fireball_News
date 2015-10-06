<?php

/**
 * @author    Jens Krumsieck
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\system\sitemap;

use cms\data\category\NewsCategoryNodeTree;
use wcf\system\sitemap\ISitemapProvider;
use wcf\system\WCF;

/**
 * Sitemap provider to list all news categories in sitemap.
 */
class NewsCategorySitemapProvider implements ISitemapProvider
{
    public $objectTypeName = 'de.codequake.cms.category.news';

    /**
     * {@inheritdoc}
     */
    public function getTemplate()
    {
        $nodeTree = new NewsCategoryNodeTree($this->objectTypeName);
        $nodeList = $nodeTree->getIterator();

        WCF::getTPL()->assign(array(
            'nodeList' => $nodeList,
        ));

        return WCF::getTPL()->fetch('newsSitemap', 'cms');
    }
}
