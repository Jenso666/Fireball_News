<?php

namespace cms\system\page\handler;
use cms\data\news\NewsCache;
use wcf\system\page\handler\AbstractLookupPageHandler;
use wcf\system\page\handler\IOnlineLocationPageHandler;
use wcf\system\WCF;

/**
 * {@inheritDoc}
 */
class NewsPageHandler extends AbstractLookupPageHandler implements IOnlineLocationPageHandler {
	use TNewsOnlineLocationPageHandler;

	/**
	 * @inheritdoc
	 */
	public function getLink($objectID) {
		return NewsCache::getInstance()->getNews($objectID)->getLink();
	}

	/**
	 * @inheritdoc
	 */
	public function isValid($objectID) {
		return (NewsCache::getInstance()->getNews($objectID) !== null);
	}

	/**
	 * @inheritdoc
	 */
	public function isVisible($objectID = null) {
		$news = NewsCache::getInstance()->getNews($objectID);

		if ($news === null)
			return false;

		return $news->isVisible();
	}

	/**
	 * @inheritdoc
	 */
	public function lookup($searchString) {
		$sql = "SELECT  newsID, subject, languageID
			FROM    cms" . WCF_N . "_news
			WHERE   subject LIKE ?
					AND (
						languageID = ?
						OR languageID IS NULL
					)";
		$statement = WCF::getDB()->prepareStatement($sql, 10);
		$statement->execute(array(
			'%' . $searchString . '%',
			WCF::getLanguage()->languageID
		));

		$results = [];
		while ($row = $statement->fetchArray()) {
			$newsID = $row['newsID'];
			$news = NewsCache::getInstance()->getNews($newsID);

			$results[] = [
				'description' => $news->getExcerpt(),
				'image' => 'fa-text-o',
				'link' => $news->getLink(),
				'objectID' => $newsID,
				'title' => $row['title']
			];
		}

		return $results;
	}
}
