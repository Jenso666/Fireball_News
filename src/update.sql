ALTER TABLE cms1_news DROP COLUMN enableSmilies;
ALTER TABLE cms1_news DROP COLUMN enableBBCodes;
ALTER TABLE cms1_news ADD COLUMN hasEmbeddedObjects TINYINT(1)   NOT NULL DEFAULT 0;

-- 1.2.0 Beta 2 || 2.0.0 Beta 3
ALTER TABLE cms1_news ADD COLUMN deletedByID     INT(10);
ALTER TABLE cms1_news ADD COLUMN deletedBy       VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE cms1_news ADD COLUMN deleteReason    INT(10)      NOT NULL DEFAULT 0;
ALTER TABLE cms1_news ADD COLUMN isDelayed       TINYINT(1)   NOT NULL DEFAULT 0;
ALTER TABLE cms1_news ADD COLUMN hasLabels       TINYINT(1)   NOT NULL DEFAULT 0;
ALTER TABLE cms1_news ADD FOREIGN KEY (deletedByID) REFERENCES wcf1_user (userID) ON DELETE SET NULL;

-- news TO user
DROP TABLE IF EXISTS cms1_news_to_user;
CREATE TABLE cms1_news_to_user (
	newsID INT(10),
	userID INT(10),

	PRIMARY KEY (userID, newsID)
);

ALTER TABLE cms1_news_to_user ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;
ALTER TABLE cms1_news_to_user ADD FOREIGN KEY (newsID) REFERENCES cms1_news (newsID) ON DELETE CASCADE;
