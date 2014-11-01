-- -----------------------------------
-- Run this SQL to upgrade the DB from 1.3 to 1.4
-- @project DkWiki
--  


ALTER TABLE `page` ADD `permissions` VARCHAR(3) NOT NULL DEFAULT '764' AFTER `score`;
UPDATE `page` SET `permissions` = '764';

ALTER TABLE `user` CHANGE `group` `groupId` INT(11) NOT NULL DEFAULT '0' COMMENT 'Required: ADMIN = 128, USER = 1';

ALTER TABLE `page` ADD `groupId` INT(3) NOT NULL DEFAULT '1' AFTER `userId`; 
 /* UPDATE `page` p, `user` u SET p.`groupId` = u.`groupId` WHERE p.`userId` = u.`id`;*/
UPDATE `page` p, `user` u SET p.`groupId` = 1;

/* Use new md5 default passwords */
UPDATE `user` set `password` = MD5(`password`) WHERE CHAR_LENGTH(`password`) < 32;



-- --------------------------------------------------------
-- 
-- Table structure for table `version`
--

INSERT INTO `version` VALUES (
NULL , '1.4', '- Implemented New DkWiki Template.
- Updated Wiki Template System. This system allows for a single html template.
- Imported updates from TkLib, ComLib and DomLib.
- Removed user avtar image.
- Fixed minor bugs in Record managers.
- Updated wiki text formatter.
- Pages use PHP5 `tidy` lib if available.
- Updated Menu editor to return to home page after edit instead of having menu display in the content area.
- Updated config.ini options
- Updated TinyMCE plugins, NewWikiPage: simplified to one field, SearchWikiPage: Fixed pager bug, FileMnager: Minor style updates and bug fixes.
- Changed how the TinyMce Editor works on the screen, removed resize ability, applied site styles to editor window.
- Added Styles dropdown to TinyMce Window.
- Split up the navigation menu to place links in logical places on the page.
- Added Page Permissions in the Unix style user/group/other
- Added jquery chmod plugin to page edit
- Added Author and group to page
- Added CSS to pages
- Added javascript to pages
- Updated user menu based on page permissions
- Updated old template to use new styles
', '2009-12-11 12:00:00', '2009-12-11 12:00:00');


