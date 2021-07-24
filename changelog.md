Tk-Wiki
=======
`Copyright (C) 2005 Michael Mifsud`


#CHANGELOG#

Ver 2.4.12 [2021-07-24]:
-------------------------------


Ver 2.4.10 [2021-07-21]:
-------------------------------


Ver 2.4.8 [2021-07-20]:
-------------------------------


Ver 2.4.4 [2019-05-12]:
-------------------------------
  - Minor Code Updates


Ver 2.4.2 [2019-04-29]:
-------------------------------
  - Updated teh code to use the newest libs
  - Upgraded to handle new form updates
  - Upgraded and updated wiki
  - Minor Code Updates
  - dev


Ver 2.4.0 [2018-07-14]:
-------------------------------
  - Tagging branch ver1 for release 2.4.0


Ver 2.2.0 [2018-01-15]:
-------------------------------
 - Fixed non-working pages
 - Minor Code Updates


Ver 2.1.6 [2017-05-27]:
-------------------------------
 - Minor Code Updates


Ver 2.1.5 [2017-05-26]:
-------------------------------
 - Minor Code Updates


Ver 2.1.4 [2017-04-02]:
-------------------------------
 - Minor Code Updates


Ver 2.1.3 [2017-04-02]:
-------------------------------
 - Minor Code Updates
 - Fixed tinymce isse with tidy removing ending tags for empty elements
 - 252s-dev.vet.unimelb.edu.au


Ver 2.1.2 [2017-03-08]:
-------------------------------
 - Minor Code Updates
 - 252s-dev.vet.unimelb.edu.au


Ver 2.1.1 [2017-02-23]:
-------------------------------
 - Minor Code Updates


Ver 2.1.0 [2017-02-23]:
-------------------------------
 - Minor Code Updates
 - 252s-dev.vet.unimelb.edu.au


Ver 2.0.21 [2017-02-22]:
-------------------------------
 - Minor Code Updates
 - 252s-dev.vet.unimelb.edu.au


Ver 2.0.20 [2017-01-20]:
-------------------------------
 - Minor Code Updates


Ver 2.0.19 [2017-01-20]:
-------------------------------
 - Minor Code Updates


Ver 2.0.18 [2017-01-20]:
-------------------------------
 - Minor Code Updates


Ver 2.0.17 [2017-01-20]:
-------------------------------
 - Fixed contributor search bug  #1
 - Minor Code Updates


Ver 2.0.16 [2017-01-16]:
-------------------------------
 - Minor Code Updates


Ver 2.0.15 [2017-01-16]:
-------------------------------
 - Minor Code Updates
 - 252s-dev.vet.unimelb.edu.au


Ver 2.0.14 [2017-01-11]:
-------------------------------
 - Minor Code Updates
 - 252s-dev.vet.unimelb.edu.au


Ver 2.0.13 [2017-01-10]:
-------------------------------
 - Updated tinymce and added slightly responcive mce layouts


Ver 2.0.12 [2017-01-10]:
-------------------------------
 - Updated tinymce and added slightly responcive mce layouts


Ver 2.0.11 [2017-01-06]:
-------------------------------
 - Fixed jquery-jtable issues


Ver 2.0.10 [2017-01-06]:
-------------------------------
 - Fixed page not found error for public view
 - Fixed css main menu issues


Ver 2.0.9 [2017-01-06]:
-------------------------------
 - Fixed page not found error for public view
 - Fixed css main menu issues
 - Fixed elFinder file manager scripts.


Ver 2.0.8 [2016-12-30]:
-------------------------------
 - Fixed elFinder file manager scripts.


Ver 2.0.7 [2016-12-30]:
-------------------------------
 - Minor Code Updates
 - Added ability to create new templates just change the config param system.template.path to your
   templates location
 - Upgraded to use new lib cass names
 - Fixed Orphaned sort bug


Ver 2.0.6 [2016-11-14]:
-------------------------------
 - Merge branch 'master' of github.com:tropotek/tkWiki
 - Minor Code Updates


Ver 2.0.5 [2016-11-14]:
-------------------------------
 - Minor Code Updates
 - 252s-dev.vet.unimelb.edu.au


Ver 2.0.4 [2016-11-11]:
-------------------------------
 - Minor Code Updates
 - Updated Wiki for new lib updates
 - Fixed page role access system
 - Added Event and plugin management pages
 - Upgraded a bit of the lib
 - Added todo.md so we can track new requirements
 - Fixed the shamozzle...
 - Fixed Form load issue for checkboxes and radios
 - Updated Wiki to use DataMaps
 - Started to add the new datamap....Finish it....
 - Changed all = [] to = array()
 - Change php version check to gt php5.0.0
 - Updated session exitsts() to has()
 - Fixed page select dialog search function
 - Added new TOC button to tineymce editor


Ver 2.0.3 [2016-07-11]:
-------------------------------
 - Added new TOC button to tineymce editor


Ver 2.0.2 [2016-07-11]:
-------------------------------


Ver 2.0.1 [2016-07-11]:
-------------------------------
 - Fixed session object


Ver 2.0.0 [2016-07-10]:
-------------------------------
 - Fixed edit javascript and tidy code
 - Added upgrade script from 1.6
 - 2.0 for MYSQL only
 - Added prompts to composer install plugin
 - Added DB backup and migration classes
 - Started adding new DB migration script to enable the installer to install new DB
 - Added installer script to setup the site
 - Fixed page access permissions
 - Fixed Responsivenes of template
 - Added new admin pages
 - Added crumbs menu
 - Added History, Search pages
 - Added Link management
 - Added Lock management
 - Fixed Menu when not logged in
 - Added Nav editing
 - Finished page Editor
 - Added more to the edit page, filemanager, wiki links, save, and more...
 - Added tinymce to edit page...
 - Added page url selector jquery plugin and updated setting page
 - Added basic view and edit controllers for wiki pages
 - Finished user login, register
 - Started Wiki pages and routing
 - More wiki updates, updated the Auth adapters too
 - Added staticMatcher for routing
 - Added new tkWiki files for v2




VER 2.0 Dev Started
===================


Ver 1.0.2 [2016-06-15]:
-------------------------------
 - Tagged github branch of old wiki to 'wikione'

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

