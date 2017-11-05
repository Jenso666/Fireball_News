-- changes WCF 2.0 > WSC 3.0
ALTER TABLE cms1_news DROP COLUMN enableSmilies;
ALTER TABLE cms1_news DROP COLUMN enableBBCodes;
ALTER TABLE cms1_news ADD COLUMN hasEmbeddedObjects TINYINT(1)   NOT NULL DEFAULT 0;

-- 1.2.0 RC 1 || 2.0.0 RC 1
ALTER TABLE cms1_news ADD COLUMN enableComments TINYINT(1) NOT NULL DEFAULT 0;
UPDATE cms1_news SET enableComments = 1;
