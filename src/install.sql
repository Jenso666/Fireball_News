-- news
DROP TABLE IF EXISTS cms1_news;
CREATE TABLE cms1_news (
	newsID             INT(10)      NOT NULL AUTO_INCREMENT PRIMARY KEY,
	userID             INT(10),
	username           VARCHAR(255),
	subject            VARCHAR(255),
	teaser             MEDIUMTEXT,
	message            MEDIUMTEXT,
	time               INT(10)      NOT NULL DEFAULT 0,
	attachments        INT(10)      NOT NULL DEFAULT 0,
	pollID             INT(10),
	languageID         INT(10),
	clicks             INT(10)      NOT NULL DEFAULT 0,
	comments           SMALLINT(5)  NOT NULL DEFAULT 0,
	imageID            INT(10),
	showSignature      TINYINT(1)   NOT NULL DEFAULT 0,
	isDisabled         TINYINT(1)   NOT NULL DEFAULT 0,
	isDeleted          TINYINT(1)   NOT NULL DEFAULT 0,
	deleteTime         INT(10)      NOT NULL DEFAULT 0,
	lastChangeTime     INT(10)      NOT NULL DEFAULT 0,
	lastEditor         VARCHAR(255) NOT NULL DEFAULT '',
	lastEditorID       INT(10)      NOT NULL DEFAULT 0,
	ipAddress          VARCHAR(39)  NOT NULL DEFAULT '',
	cumulativeLikes    INT(10)      NOT NULL DEFAULT 0,
	hasEmbeddedObjects TINYINT(1)   NOT NULL DEFAULT 0,
	enableHtml         TINYINT(1)   NOT NULL DEFAULT 0,
	deletedByID     INT(10),
	deletedBy       VARCHAR(255) NOT NULL DEFAULT '',
	deleteReason    INT(10)      NOT NULL DEFAULT 0,
	isDelayed       TINYINT(1)   NOT NULL DEFAULT 0,
	hasLabels       TINYINT(1)   NOT NULL DEFAULT 0
);

-- news TO user
DROP TABLE IF EXISTS cms1_news_to_user;
CREATE TABLE cms1_news_to_user (
	newsID INT(10),
	userID INT(10),

	PRIMARY KEY (userID, newsID)
);

-- news TO category
DROP TABLE IF EXISTS cms1_news_to_category;
CREATE TABLE cms1_news_to_category (
	categoryID INT(10),
	newsID     INT(10),

	PRIMARY KEY (categoryID, newsID)
);

-- FOREIGN KEYS
ALTER TABLE cms1_news
	ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID)
	ON DELETE SET NULL;
ALTER TABLE cms1_news
	ADD FOREIGN KEY (languageID) REFERENCES wcf1_language (languageID)
	ON DELETE SET NULL;
ALTER TABLE cms1_news
	ADD FOREIGN KEY (imageID) REFERENCES cms1_file (fileID)
	ON DELETE SET NULL;
ALTER TABLE cms1_news
	ADD FOREIGN KEY (pollID) REFERENCES wcf1_poll (pollID)
	ON DELETE SET NULL;
ALTER TABLE cms1_news
	ADD FOREIGN KEY (deletedByID) REFERENCES wcf1_user (userID)
	ON DELETE SET NULL;

ALTER TABLE cms1_news_to_category
	ADD FOREIGN KEY (categoryID) REFERENCES wcf1_category (categoryID)
	ON DELETE CASCADE;
ALTER TABLE cms1_news_to_category
	ADD FOREIGN KEY (newsID) REFERENCES cms1_news (newsID)
	ON DELETE CASCADE;

ALTER TABLE cms1_news_to_user
	ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID)
	ON DELETE CASCADE;
ALTER TABLE cms1_news_to_user
	ADD FOREIGN KEY (newsID) REFERENCES cms1_news (newsID)
	ON DELETE CASCADE;
