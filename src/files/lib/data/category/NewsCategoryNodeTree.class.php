<?php

/**
 * @author    Jens Krumsieck
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\data\category;

use wcf\data\category\CategoryNodeTree;

/**
 * Represent a tree of news category nodes.
 */
class NewsCategoryNodeTree extends CategoryNodeTree
{
    /**
     * {@inheritdoc}
     */
    protected $nodeClassName = 'cms\data\category\NewsCategoryNode';
}
