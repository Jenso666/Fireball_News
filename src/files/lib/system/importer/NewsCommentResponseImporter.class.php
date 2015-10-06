<?php

/**
 * @author    Jens Krumsieck
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\system\importer;

use wcf\system\importer\AbstractCommentResponseImporter;

/**
 * Importer for news comment responses.
 */
class NewsCommentResponseImporter extends AbstractCommentResponseImporter
{
    /**
     * {@inheritdoc}
     */
    protected $objectTypeName = 'de.codequake.cms.news.comment';
}
