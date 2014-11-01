-- -----------------------------------
-- DkWiki install-data.sql
-- 
-- 

INSERT INTO `user` (`id`, `name`, `email`, `image`, `active`, `username`, `password`, `groupId`, `modified`, `created`) VALUES
(NULL, 'Administrator', 'admin@example.com', '', 1, 'admin@example.com', MD5('password'), 128, NOW(), NOW());

INSERT INTO `page` VALUES
(1, 1, 1, 1, 'Home', 'Home', '', '', '', 0, 0, 0.0, '764', 0, NOW(), NOW()),
(2, 2, 1, 1, 'Menu', 'Menu', '', '', '', 0, 0, 0.0, '764', 0, NOW(), NOW());

INSERT INTO `text` VALUES
(1, 1, 1, '<h3>Welcome To DkWIKI</h3>\r\n<p>We have been working on a WIKI that is easy to use. This resulted us in using plain HTML for the WIKI syntax. So if you are comfortable using HTML and the WYSIWYG editors. Then this is the WIKI for you!</p>\r\n<p>We use it for all ort group/client projects to improve communication and keep new ideas documented.</p>\r\n<p>Please use our forum at <a href="http://forum.tropotek.com/" target="_blank">http://forum.tropotek.com/</a> to request new feature, inform us of any bugs or even to find existing answers to any questions you may have.</p>\r\n<p>&nbsp;</p>\r\n<hr />\r\n<h3>Style Examples</h3>\r\n<p>Etiam mauris? Quisque eget justo a lectus elementum gravida. Maecenas tellus elit, euismod ut, faucibus in, lobortis quis, nibh. Proin aliquam, dui nec scelerisque condimentum, quam augue tincidunt elit, eget cursus velit ante nec dui. Quisque tempor mattis mauris. Suspendisse dui justo, rhoncus vitae, elementum interdum, pellentesque tristique, nunc. Praesent ipsum neque, molestie et, egestas nec, interdum quis, ligula! Proin malesuada, tellus vel condimentum bibendum, augue magna volutpat purus, eget dapibus dolor dui sed sem. Nunc id eros non nulla placerat adipiscing. Quisque in lacus ut velit aliquam interdum.</p>\r\n<h3>Pre<br /></h3>\r\n<pre>Vestibulum venenatis pellentesque quam. Nam mattis magna et purus. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Aliquam id velit. Nam ut justo ornare enim varius vulputate. Nunc nec quam. Curabitur dictum. Mauris nisl. Nam sagittis tincidunt erat? <br />Sed elementum hendrerit nibh. Nam rhoncus. Duis augue sem, pharetra non, sodales quis, posuere nec, quam. <br />In pretium. Aenean mauris. Nullam tempus leo tincidunt orci. Nam vestibulum eros in magna. Donec metus!</pre>\r\n<p>Aliquam lectus erat, mattis ut, tristique at, ultrices eu, erat. Ut vulputate, dui quis fringilla rhoncus, quam ipsum laoreet diam, eget bibendum quam ligula semper lectus. Vestibulum porttitor sapien sit amet quam? Proin sit amet elit. Pellentesque et elit. Etiam facilisis, nisi et aliquet adipiscing, dolor tellus ullamcorper mauris, non tincidunt leo velit vel velit? Nam eu libero! Nullam feugiat diam id tortor! Nulla facilisi. Aliquam leo arcu, cursus et, facilisis sed; interdum sed, orci. Donec velit? Donec egestas, erat et accumsan mattis, arcu lorem gravida lorem, eu bibendum lorem ante quis lorem. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Pellentesque mollis, neque in mollis ultrices, ipsum sapien gravida sapien, non tristique sem augue ultricies urna.</p>\r\n<h3>Blockquote<br /></h3>\r\n<blockquote>\r\n<p>Nullam nec sem. Nam vitae purus. Vivamus convallis? Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Duis auctor lectus et arcu. Curabitur elementum leo ac urna. In et quam ac risus sodales placerat. Curabitur semper, purus ac vehicula consequat, sapien nibh ornare turpis, consequat pretium nisi ligula vel tellus. Morbi molestie diam. Suspendisse rutrum nisi vel sapien. Fusce aliquam porta odio. Praesent at est. Duis tempor. Mauris id ante vitae lorem faucibus interdum. Etiam condimentum mauris non nibh. Aliquam erat volutpat. Integer lectus leo; egestas non; dictum sed, pulvinar quis, dui.</p>\r\n</blockquote>\r\n<p>Nunc suscipit dui in diam. Nullam eget diam. Nullam ut felis? Curabitur imperdiet. Nullam vulputate. Aliquam erat volutpat. Nulla sit amet libero quis augue pulvinar tincidunt? Maecenas ut sem at nisl viverra ultricies. Pellentesque scelerisque orci sed leo. Maecenas turpis mauris, faucibus sit amet, semper ut, pharetra vel, quam. Quisque lacus.</p>\r\n<h3>Lists</h3>\r\n<ul>\r\n<li>Item 1</li>\r\n<li>Item 2</li>\r\n<li>Item 3</li>\r\n<li>Item 4</li>\r\n</ul>\r\n<ol>\r\n<li>Item 1</li>\r\n<li>Item 2</li>\r\n<li>Item 3</li>\r\n<li>Item 4</li>\r\n</ol>\r\n<p>Aenean sit amet purus id mi suscipit posuere. Suspendisse eget magna vitae metus euismod laoreet. Aenean neque ipsum, luctus a, gravida id, venenatis a, lectus. Morbi eu dui id nisl tincidunt sagittis. Nullam laoreet. Proin mollis urna at dui? Mauris auctor augue placerat libero. Suspendisse leo. Praesent commodo gravida erat. Sed ullamcorper lorem hendrerit ligula. In rhoncus nisl at ligula.</p>\r\n<p>&nbsp;</p>', NOW()),
(2, 2, 1, '<h3>Menu</h3>\r\n<ul>\r\n<li><a class="wikiPage" title="Link To Page: Home" href="page://Home">Home</a></li>\r\n<li><a href="login.html">Login</a></li>\r\n</ul>', NOW());


INSERT INTO `version` VALUES (
NULL , '1.0', '- Initial project release
- Updated Calls to Pager, Limit objects
- Updated calls to database
- Added new TinyMCE file manager plugin', 
'2009-01-01 12:00:00', '2009-01-01 12:00:00'
),
(NULL , '1.2', '- Added a basic user management system', 
'2009-01-02 12:00:00', '2009-01-02 12:00:00'
),
(NULL , '1.3', '- Updated login to stay on page you logged in on.
- Added new default template skin
- Added css skin directory
- Updated misc admin manager information to display correct values
- Added version table
- Added new install/upgrade script
- Updated TinyMCE filemanager plugin
- Added page RSS feed to allow users to monitor page updates.
- Optimised SQL tables
- Added publisher and contributor information to pages
- Updated page layout to include published avatar image
- Updated search engine', 
'2009-04-01 14:54:23', '2009-04-01 14:54:23'),
(
NULL , '1.4', '- Implemented New DkWiki Template.
- Updated Wiki Template System. This system allows for a single html template.
- Imported updates from TkLib, ComLib and DomLib.
- Removed user avtar image.
- Fixed minor bugs in Record managers.
- Updated wiki text formatter.
- Pages use PHP5 `tidy` lib if available.
- Updated Menu editor to return to home page after edit instead of having menu display in the content area.
- Updated  config.ini options
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
- Updated old template to use new styles ', 
'2009-12-11 12:00:00', '2009-12-11 12:00:00'),
(
NULL , '1.5', '- Fixed orphan bug (Must re-save pages [Menu/Home] to fix)
- Fixed CSS for image caption
- Fixed Page error for non-logged in users when a page does not exist,
- Replaced lib/Ext regex functions with preg_match equivalents (ready for PHP5.3)
- Upgraded TkLib/ComLib/JsLib sub-libs
- Added page comments',
'2010-08-14 12:00:00', '2010-08-14 12:00:00'
);

INSERT INTO `version` VALUES (
NULL , '1.6', '- Fixed orphan bug (Must resave pages [Menu/Home] to fix)
- Updated Forms to use the new Form module
- Updated Tabled to use the new Table module
- Updated login system to use the new Auth module
- Added new settings object
- Added notify email to SiteEmail on new comments cna be turned off in config.ini',
'2011-07-12 12:00:00', '2011-07-12 12:00:00');
