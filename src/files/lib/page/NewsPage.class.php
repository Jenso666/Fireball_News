<?php

/**
 * @author    Jens Krumsieck
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\page;

use cms\data\category\NewsCategory;
use cms\data\news\NewsAction;
use cms\data\news\NewsEditor;
use cms\data\news\ViewableNews;
use cms\system\counter\VisitCountHandler;
use wcf\page\AbstractPage;
use wcf\system\comment\CommentHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\like\LikeHandler;
use wcf\system\MetaTagHandler;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Page for news entries.
 */
class NewsPage extends AbstractPage {
	/**
	 * id of the news
	 * @var integer
	 */
	public $newsID = 0;

	/**
	 * @var ViewableNews|\cms\data\news\News
	 */
	public $news = null;

	/**
	 * id of the comment's objectType
	 * @var integer
	 */
	public $commentObjectTypeID = 0;

	/**
	 * @var \cms\system\comment\manager\NewsCommentManager
	 */
	public $commentManager = null;

	/**
	 * @var \wcf\data\comment\StructuredCommentList;
	 */
	public $commentList = [];

	/**
	 * like-data of comments
	 * @var []
	 */
	public $likeData = [];

	/**
	 * list of tags
	 * @var []
	 */
	public $tags = [];

	/**
	 * @inheritDoc
	 *
	 * @throws \wcf\system\exception\IllegalLinkException if no id provided with this request or no news with the given
	 *                                                    id exists.
	 * @throws \wcf\system\exception\PermissionDeniedException if news is assigned to a category the current user
	 *                                                         cannot read.
	 */
	public function readParameters() {
		parent::readParameters();

		if (isset($_REQUEST['id'])) $this->newsID = intval($_REQUEST['id']);
		$this->news = ViewableNews::getNews($this->newsID);

		if ($this->news === null || !$this->news->newsID) {
			throw new IllegalLinkException();
		}

		/** @var NewsCategory $category */
		foreach ($this->news->getCategories() as $category) {
			if (!$category->getPermission('canViewNews')) {
				throw new PermissionDeniedException();
			}
		}
	}

	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();

		VisitCountHandler::getInstance()->count();

		$this->commentObjectTypeID = CommentHandler::getInstance()->getObjectTypeID('de.codequake.cms.news.comment');
		$this->commentManager = CommentHandler::getInstance()->getObjectType($this->commentObjectTypeID)->getProcessor();
		$this->commentList = CommentHandler::getInstance()->getCommentList($this->commentManager, $this->commentObjectTypeID, $this->newsID);

		$newsEditor = new NewsEditor($this->news->getDecoratedObject());
		$newsEditor->updateCounters(array('clicks' => 1,));

		// get Tags
		if (MODULE_TAGGING) {
			$this->tags = $this->news->getTags();
		}
		if ($this->news->teaser != '') {
			MetaTagHandler::getInstance()->addTag('description', 'description', $this->news->teaser);
		} else {
			MetaTagHandler::getInstance()->addTag('description', 'description', StringUtil::decodeHTML(StringUtil::stripHTML($this->news->getExcerpt())));
		}

		if (!empty($this->tags)) {
			MetaTagHandler::getInstance()->addTag('keywords', 'keywords', implode(',', $this->tags));
		}
		MetaTagHandler::getInstance()->addTag('og:title', 'og:title', $this->news->subject . ' - ' . WCF::getLanguage()->get(PAGE_TITLE), true);
		MetaTagHandler::getInstance()->addTag('og:url', 'og:url', LinkHandler::getInstance()->getLink('News', array(
			'application' => 'cms',
			'object' => $this->news->getDecoratedObject(),
		)), true);
		MetaTagHandler::getInstance()->addTag('og:type', 'og:type', 'article', true);
		if ($this->news->getImage() != null) {
			MetaTagHandler::getInstance()->addTag('og:image', 'og:image', $this->news->getImage()->getLink(), true);
		}
		if ($this->news->getUserProfile()->facebook != '') {
			MetaTagHandler::getInstance()->addTag('article:author', 'article:author', 'https://facebook.com/' . $this->news->getUserProfile()->facebook, true);
		}
		if (FACEBOOK_PUBLIC_KEY != '') {
			MetaTagHandler::getInstance()->addTag('fb:app_id', 'fb:app_id', FACEBOOK_PUBLIC_KEY, true);
		}
		MetaTagHandler::getInstance()->addTag('og:description', 'og:description', StringUtil::decodeHTML(StringUtil::stripHTML($this->news->getExcerpt())), true);

		if ($this->news->isNew()) {
			$newsAction = new NewsAction(array($this->news->getDecoratedObject()), 'markAsRead', array('viewableNews' => $this->news));
			$newsAction->executeAction();
		}

		// fetch likes
		if (MODULE_LIKE) {
			$objectType = LikeHandler::getInstance()->getObjectType('de.codequake.cms.likeableNews');
			LikeHandler::getInstance()->loadLikeObjects($objectType, array($this->newsID,));
			$this->likeData = LikeHandler::getInstance()->getLikeObjects($objectType);
		}
	}

	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();

		WCF::getTPL()->assign(array(
			'newsID' => $this->newsID,
			'news' => $this->news,
			'likeData' => ((MODULE_LIKE && $this->commentList) ? $this->commentList->getLikeData() : array()),
			'newsLikeData' => $this->likeData,
			'commentCanAdd' => (WCF::getUser()->userID && WCF::getSession()->getPermission('user.cms.news.canAddComment')),
			'commentList' => $this->commentList,
			'commentObjectTypeID' => $this->commentObjectTypeID,
			'tags' => $this->tags,
			'lastCommentTime' => ($this->commentList ? $this->commentList->getMinCommentTime() : 0),
			'attachmentList' => $this->news->getAttachments(),
			'allowSpidersToIndexThisPage' => true
		));
	}
}
