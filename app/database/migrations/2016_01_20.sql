

ALTER TABLE `courses`
DROP COLUMN `credits`,
ADD `old` BOOLEAN NULL DEFAULT FALSE,
DROP INDEX `string_id_UNIQUE`;

ALTER TABLE `course_instances`
ADD `lang` ENUM('en','fr','de') NOT NULL,
DROP COLUMN `term`,
CHANGE `credits` `credits` TINYINT(4) NULL;

UPDATE `course_instances` SET `credits`=NULL WHERE `credits`=0;
