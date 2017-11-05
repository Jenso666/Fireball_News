ALTER TABLE cms1_news ADD COLUMN enableComments TINYINT(1) NOT NULL DEFAULT 0;
UPDATE cms1_news SET enableComments = 1;
