Tk-Wiki
=======
`Copyright (C) 2005 Michael Mifsud`


#CHANGELOG#

Ver 1.0.2 [2016-06-15]:
-------------------------------


Ver 1.0.1 [2015-03-04]:
-------------------------------
 - Cleaning up files
 - Made SSL off by default.


Ver 1.0.0 [2015-03-04]
-------------------------------

 - Released first github version of the site
 - All file in the lib folder are now static
 - Implemented composer into project
 - Fixed default SSL enabled to default SSL disabled, enable by the config.
 - Fixed template encoding errors
 - Fixed session file issues (temp fix only)
 


PRE GITHUB RELEASES.
====================

Ver 1.5:
--------

- Fixed orphan bug (Must resave pages [Menu/Home] to fix)
- Fixed CSS for image caption
- Fixed Page error for non-logged in users when a page does not exist,
- Replaced lib/Ext regex functions with preg_match equivalents (ready for PHP5.3)
- Upgraded TkLib/ComLib/JsLib sub-libs

Ver 1.4:
--------

- Implemented New DkWiki Template.
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
- Updated old template to use new styles


Wednesday, 1 Apr 2009 02:54 PM - Ver 1.3:
-----------------------------------------

- Updated login to stay on page you logged in on.
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

Friday, 2 Jan 2009 12:00 PM - Ver 1.2:
--------------------------------------

- Added a basic user management system

Thursday, 1 Jan 2009 12:00 PM - Ver 1.0:
----------------------------------------

- Initial project release
- Updated Calls to Pager, Limit objects
- Updated calls to database
- Added new TinyMCE file manager plugin

