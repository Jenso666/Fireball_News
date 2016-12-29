ALTER TABLE cms1_news DROP COLUMN enableSmilies;
ALTER TABLE cms1_news DROP COLUMN enableHtml;
ALTER TABLE cms1_news DROP COLUMN enableBBCodes;
ALTER TABLE cms1_news ADD COLUMN hasEmbeddedObjects TINYINT(1)   NOT NULL DEFAULT 0;
