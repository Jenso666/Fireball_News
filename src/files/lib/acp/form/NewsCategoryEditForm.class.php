<?php

/**
 * @author    Jens Krumsieck
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\acp\form;

use wcf\acp\form\AbstractCategoryEditForm;

/**
 * Shows the news category edit form.
 */
class NewsCategoryEditForm extends AbstractCategoryEditForm
{
    /**
     * {@inheritdoc}
     */
    public $activeMenuItem = 'cms.acp.menu.link.cms.news.category.add';

    /**
     * {@inheritdoc}
     */
    public $objectTypeName = 'de.codequake.cms.category.news';

    /**
     * {@inheritdoc}
     */
    public $pageTitle = 'wcf.category.edit';
}
