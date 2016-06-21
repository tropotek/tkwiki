

INSERT INTO "user" (name, email, username, password, active, hash, modified, created) VALUES
('Administrator', 'admin@example.com', 'admin', md5('password'), TRUE, MD5(CONCAT('admin@example.com', date_trunc('seconds', NOW()))), date_trunc('seconds', NOW()) , date_trunc('seconds', NOW())),
('Moderator', 'modder@example.com', 'modder', md5('password'), TRUE, MD5(CONCAT('modder@example.com', date_trunc('seconds', NOW()))), date_trunc('seconds', NOW()) , date_trunc('seconds', NOW())),
('User', 'user@example.com', 'user', md5('password'), TRUE, MD5(CONCAT('user@example.com', date_trunc('seconds', NOW()))), date_trunc('seconds', NOW()) , date_trunc('seconds', NOW()))
;

INSERT INTO role (name, description) VALUES
('admin', 'Manage site, groups, users, pages, etc no restrictions'),
('moderator', 'Manage assigned users and pages for assigned groups'),
('user', 'Manage user settings, pages only'),
('create', 'Create pages'),
('edit', 'Edit existing pages'),
('delete', 'Delete pages'),
('editExtra', 'Can edit page css, js, url and template options')
;

INSERT INTO user_role (user_id, role_id)
VALUES
  -- Administrator
  (1, 1),(1, 2),(1, 3),(1, 4),(1, 5),(1, 6),(1, 7),
  -- Moderator
  (2, 2),(2, 3),(2, 4),(2, 5),(2, 6),(2, 7),
  -- User
  (3, 3),(3, 4),(3, 5),(3, 6),(3, 7)
;


INSERT INTO data (foreign_id, foreign_key, key, value) VALUES
  (0, 'system', 'site.title', 'TkWiki II'),
  (0, 'system', 'site.email', 'tkwiki@example.com'),
  (0, 'system', 'site.meta.keywords', ''),
  (0, 'system', 'site.meta.description', ''),
  (0, 'system', 'site.global.js', ''),
  (0, 'system', 'site.global.css', '')
;


-- --------------------------------------
-- Table data: version 
-- --------------------------------------
INSERT INTO "version" (version, changelog, modified, created) VALUES  
('1.0', '- Initial project release
- Updated Calls to Pager, Limit objects
- Updated calls to database
- Added new TinyMCE file manager plugin',
'2009-01-01 12:00:00', '2009-01-01 12:00:00'),
('1.2', '- Added a basic user management system',
'2009-01-02 12:00:00', '2009-01-02 12:00:00'),
('1.3', '- Updated login to stay on page you logged in on.
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
('1.4', '- Implemented New DkWiki Template.
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
('1.5', '- Fixed orphan bug (Must re-save pages [Menu/Home] to fix)
- Fixed CSS for image caption
- Fixed Page error for non-logged in users when a page does not exist,
- Replaced lib/Ext regex functions with preg_match equivalents (ready for PHP5.3)
- Upgraded TkLib/ComLib/JsLib sub-libs
- Added page comments',
'2010-08-14 12:00:00', '2010-08-14 12:00:00'
),
('1.6', '- Fixed orphan bug (Must re-save pages [Menu/Home] to fix)
- Updated Forms to use the new Form module
- Updated Tabled to use the new Table module
- Updated login system to use the new Auth module
- Added new settings object
- Added notify email to SiteEmail on new comments cna be turned off in config.ini',
'2011-07-12 12:00:00', '2011-07-12 12:00:00'),
('2.0', '- Version 2.0 released
- Completely re-written codebase to use new PHP5.3+
- Added new postgress DB files',
'2016-06-01 12:00:00', '2016-06-01 12:00:00');






