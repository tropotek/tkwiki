-- --------------------------------------------
-- @version 8.0.0 install
-- --------------------------------------------


-- A page container
CREATE TABLE IF NOT EXISTS page
(
  id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT(11) UNSIGNED NULL,
  template VARCHAR(64) NOT NULL DEFAULT '',
  category VARCHAR(128) NOT NULL DEFAULT '',
  title VARCHAR(255) NOT NULL DEFAULT '',
  url VARCHAR(255) NULL,
  views INT(11) UNSIGNED NOT NULL DEFAULT 0,
  permission INT NOT NULL DEFAULT 0,
  title_visible BOOL NOT NULL DEFAULT TRUE,
  published BOOL NOT NULL DEFAULT TRUE,
  modified TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uk_url (url),
  KEY k_user_id (user_id),
  KEY k_category (category),
  CONSTRAINT fk_page__user_id FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL
);

-- Store the content of each page revision
CREATE TABLE IF NOT EXISTS content
(
  id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  page_id INT(11) UNSIGNED NOT NULL DEFAULT 0,
  user_id INT(11) UNSIGNED NULL,
  html LONGTEXT NOT NULL,
  keywords VARCHAR(255) NOT NULL DEFAULT '',
  description VARCHAR(255) NOT NULL DEFAULT '',
  css TEXT,
  js TEXT,
  created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FULLTEXT KEY ft_html (html),
  KEY k_page_id (page_id),
  KEY k_user_id (user_id),
  CONSTRAINT fk_content__page_id FOREIGN KEY (page_id) REFERENCES page (id) ON DELETE CASCADE,
  CONSTRAINT fk_content__user_id FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL
);

-- wiki links contained within a pages html content
CREATE TABLE IF NOT EXISTS links
(
  page_id INT(11) UNSIGNED NOT NULL DEFAULT 0,
  url VARCHAR(255) NOT NULL DEFAULT '',
  UNIQUE KEY uk_page_id_url (page_id, url)
);

-- place to store a page lock while it is being edited.
-- Only one user can edit a page at a time
CREATE TABLE IF NOT EXISTS `lock` (
    hash VARCHAR(64) NOT NULL DEFAULT '',
    page_id INT(11) UNSIGNED NOT NULL DEFAULT 0,
    user_id INT(11) UNSIGNED NOT NULL DEFAULT 0,
    ip VARCHAR(64) NOT NULL DEFAULT '',
    expire TIMESTAMP NOT NULL,
    CONSTRAINT fk_lock__page_id FOREIGN KEY (page_id) REFERENCES page (id) ON DELETE CASCADE,
    CONSTRAINT fk_lock__user_id FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE,
    UNIQUE KEY uk_hash (hash),
    KEY k_pageId (page_id),
    KEY k_userId (user_id)
);


CREATE TABLE IF NOT EXISTS menu_item
(
  menu_item_id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  parent_id INT(11) UNSIGNED NULL,
  page_id INT(11) UNSIGNED NULL,
  order_id INT(11) UNSIGNED NOT NULL DEFAULT 0,
  type enum('item','dropdown','divider') NOT NULL DEFAULT 'item',
  name VARCHAR(255) NOT NULL DEFAULT '',
  KEY k_page_id (page_id),
  CONSTRAINT fk_menu_item__parent_id FOREIGN KEY (parent_id) REFERENCES menu_item (menu_item_id) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT fk_menu_item__page_id FOREIGN KEY (page_id) REFERENCES page (page_id) ON DELETE CASCADE
);


CREATE TABLE IF NOT EXISTS secret (
    secret_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL DEFAULT 0,                      -- Author of the key
    permission INT NOT NULL DEFAULT 0,                            -- Same as page permissions (no public permission)
    name VARCHAR(64) NOT NULL DEFAULT '',                         -- A name for this secret record
    url VARCHAR(128) NOT NULL DEFAULT '',                         -- The url to the website that this auth record is for
    username VARCHAR(128) NOT NULL DEFAULT '',                    -- (encoded)
    password VARCHAR(128) NOT NULL DEFAULT '',                    -- (encoded)
    otp VARCHAR(128) NOT NULL DEFAULT '',                         -- (encoded) OTP/Google auth key: wen set we can generate onetime 2FA keys
    `keys` TEXT,                                                  -- (encoded) could be a wallet key, or API key, public/private keys
    notes TEXT,                                                   --
    modified TIMESTAMP NOT NULL,
    created TIMESTAMP NOT NULL,
    KEY k_user_id (user_id),
    -- TODO: be sure to move all non private secrets to the auth user on user deletes if we want to keep them????
    CONSTRAINT fk_secret__user_id FOREIGN KEY (user_id) REFERENCES user (user_id) ON DELETE CASCADE
);



-- Site default content

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_SAFE_UPDATES = 0;

TRUNCATE TABLE user;
INSERT INTO user (type, username, email, name, timezone, permissions) VALUES
  ('staff', 'admin', 'admin@example.com', 'Administrator', NULL, 1)
;
UPDATE `user` SET `hash` = MD5(CONCAT(username, user_id)) WHERE 1;



TRUNCATE TABLE page;
TRUNCATE TABLE content;
INSERT INTO page (user_id, category, title, url, permission) VALUES
  (1, 'Wiki', 'Home', 'home', 0),
  (1, 'Wiki', 'Wiki How To', 'Wiki_How_To', 0),
  (1, 'Wiki', 'Example Content', 'Example_Content', 0)
;
INSERT INTO content (page_id, user_id, html, css, js) VALUES
    (1, 1, '<h2>Welcome to the WIKI</h2>
<p>This is the default homepage of you new WIKI. Start adding content and building your own content.</p>
<p><a title="Wiki How To" href="page://Wiki_How_To">Wiki How To</a></p>
<p><a title="Example Content" href="page://Example_Content">Example Content</a></p>
<p>&nbsp;</p>', '', ''),
  (2, 1, '<p><strong>Contents:</strong></p>
<ul>
<li><a href="#about_this_wiki">About this wiki</a></li>
<li><a href="#getting_started">Getting Started</a></li>
<li><a href="#page_and_user_permissions">Page and user permissions</a></li>
<li><a href="#creating_a_page">Creating a page</a></li>
<li><a href="#basic_page_editing">Basic page editing</a></li>
<li><a href="#editing_the_menu">Editing the menu</a><a href="#advanced_page_editing"></a></li>
<li><a href="#reverting_page_content">Reverting page content</a></li>
<li><a href="#finding_orphaned_pages">Finding orphaned pages</a></li>
<li><a href="#site_settings">Site settings</a></li>
<li><a href="#user_management">User Management</a></li>
<li><a href="page://Example_Content" title="Example Content">Example Content</a></li>
</ul>
<p>&nbsp;</p>
<h3>About this wiki<a name="about_this_wiki"></a></h3>
<p>This wiki is intended to be a place for an organization to document information and share that information internally and externally. It uses a basic WYSIWYG editor to edit HTML content and should allow you to create pages as simple or as complex as your experience with HTML will allow.</p>
<p><strong>Features include:</strong></p>
<ul>
<li>Create HTML pages with CSS and JavaScript if required</li>
<li>Easily create and link wiki pages</li>
<li>Manage page versions history, revert to past versions if needed</li>
<li>Upload media files and link them into your pages</li>
<li>Allow/deny access to pages by user type</li>
<li>Edit the top menu bar with a drag and drop editor</li>
<li>Fulltext search function to find pages</li>
<li>Find and manage orphaned pages</li>
<li>For the more adventurous, modify the page template and scripts to your own branding</li>
<li>Manage site settings</li>
<li>A basic contact page is also available</li>
</ul>
<p>&nbsp;</p>
<h3>Getting Started<a name="#getting_started"></a></h3>
<p>Once installed and working it is a good idea to create yourself a new <strong>staff</strong> account, so that the pages you create have your name as the author. See the menu (top right) in the drop-down select ''Staff'' option, make sure to use a valid email so that you can activate the account and setup the new password. Also give yourself ADMIN permissions so you can manage the site.</p>
<p>&nbsp;</p>
<h3>Page and user permissions<a name="page_and_user_permissions"></a></h3>
<p>Before getting into creating any content, its important to understand how this wiki''s permission system works. It has been kept as simple as possible to be flexible yet easy to use.</p>
<p><strong>User Types</strong></p>
<p>There are two types of users that can be created a ''user'' type and a ''staff'' type.&nbsp;</p>
<p>A user does not have any special additional permissions and is generally a self registered user that has used the online create account registration form. They could be considered as your organizations clients.</p>
<p>The second type of user is ''staff''. A ''staff'' user can have extra permissions such as full administration permissions or page edit (editor) permissions. They can also be giving other permissions to manage the site, user accounts and or staff accounts.</p>
<p><strong>Page Permissions</strong></p>
<p>Now that we understand the user types and permissions we discuss how they relate to ''Page Permissions''.</p>
<p>Pages have four permission types and affect a users ability to view, edit, or delete a page:</p>
<ul>
<li><strong>Public</strong>: Viewable by <strong>anyone</strong>, only <strong>staff</strong> can edit these pages</li>
<li><strong>User</strong>: Viewable by <strong>registered</strong> users (<strong>user/staff</strong>), only <strong>staff</strong> can edit these pages</li>
<li><strong>Staff</strong>: Viewable by <strong>staff</strong> users, only <strong>editors</strong> can edit these pages</li>
<li><strong>Private</strong>: Only the page <strong>author</strong> can view/edit these pages</li>
</ul>
<p><strong>Note:</strong> Staff users with ''Admin Full Access'' permission have full read write access to the wiki and its pages ignoring any page permissions.</p>
<p>&nbsp;</p>
<h3>Creating a page<a name="creating_a_page"></a></h3>
<p>You create a new page by first editing an existing page. If this is your first page, on the homepage of the wiki click edit and we can create our first page there.</p>
<p>Within the WYSIWYG editor the first toolbar button (<img src="/html/assets/img/help/wikiCreate.png" alt="wiki create icon">) allows you to select an existing wiki page or create a new one. Place your cursor where you wish to create the new page link and click the new page icon.</p>
<p>In the dialog footer there is a "Create" button with a text field to enter a new page title. Enter a title and click the create button:</p>
<p><img src="/html/assets/img/help/createDialog.png" alt="wiki create dialog"></p>
<p>This action will insert a red link into your page. At this point the page still does not exist, red links are just placeholders to potential new pages. You can create them yourself with the markup:</p>
<pre class="language-markup"><code>&amp;lt;a href="page://new_page_url"&amp;gt;New Page Url&amp;lt;/a&amp;gt;</code></pre>
<p>All wiki URL''s have an internal structure of ''page://'' then appended with the desired URL. If the page url exists in the wiki already it will link to the existing page.</p>
<p>&nbsp;</p>
<h3>Basic page editing<a name="basic_page_editing"></a></h3>
<p>We will not go into how to use the WYSIWYG editor but you can look it up yourself starting at <a href="https://www.tiny.cloud/tinymce/features/">https://www.tiny.cloud/tinymce/features/</a>.&nbsp;</p>
<p>The editor also has a file manager where you can upload media files. All pages share the same directory for files so be sure to structure all files within sub-folders so they can be found by everyone.</p>
<p>Be careful of what files you upload, as all staff users will have access to them, although your page data may be private, any files you upload will be accessible by all staff and accessible globally through their URL''s.</p>
<p>&nbsp;</p>
<h3>Editing the menu<a name="editing_the_menu"></a></h3>
<p>Click the username menu in the top right, the find the "Menu Edit" option.</p>
<p><img src="/html/assets/img/help/editMenu.png" alt="Edit menu"></p>
<ol>
<li>Add a Page, Dropdown or Divider menu item. Dropdown items can contain pages and dividers.</li>
<li>Use the handles to drag your menu item into place, only one level of nesting is enabled.</li>
<li>You can rename the Page and Dropdown items, click on the name to change it.</li>
<li>Delete the item, all child items will be deleted to so move them if you want to keep them.</li>
</ol>
<p>The editor will save the menu automatically after each action.</p>
<p>&nbsp;</p>
<h3>Reverting page content<a name="reverting_page_content"></a></h3>
<p>The wiki retains a revision history of each pages content. This allows us to revert back to a previous revision if required.</p>
<p>View a page that you wish to view the revision history for and in the top page toolbar button group you will see the <img src="/html/assets/img/help/historyBtn.png" alt="wiki create dialog"> button. Click this to see this pages revision history.</p>
<p>Form this page you can view and revert any past revision as needed.</p>
<p>&nbsp;</p>
<h3>Finding orphaned pages<a name="finding_orphaned_pages"></a></h3>
<p>In the username drop-down menu find the "Orphaned Pages" item.</p>
<p>From here a user will be able to find any pages they have access to that are not linked to by any existing pages.</p>
<p>This function is handy when auditing all pages and seeing if there are any that are no longer relevant and my require removal as they are not visible to anyone.</p>
<p>&nbsp;</p>
<h3>Site settings<a name="site_settings"></a></h3>
<p>The settings page has a number of options that you will find useful when administering your new wiki. You can find it in the username drop-down menu on the top right of the page.</p>
<p>From the setting page you can:</p>
<ul>
<li>Change the site title</li>
<li>Enable/Disable public user account registration</li>
<li>Change the default home wiki page</li>
<li>Set the site email address and email signature content</li>
<li>Add SEO meta data for keywords and descriptions which will be the default if a wiki page does not have any entered</li>
<li>Add custom JavaScript to all pages</li>
<li>Add custom CSS styles to all pages</li>
<li>Enable/Disable maintenance mode and its public message</li>
</ul>
<p>&nbsp;</p>
<h3>User Management<a name="user_management"></a></h3>
<p>From the top username menu drop-down you have the ability to manage Staff and Users. You can create/edit/delete and masquerade as users depending on your user permissions.</p>
<p>When creating a new user from the user manger page, be sure to enter a valid email for that user as they will receive an email instructing them to follow a link to create their new account password. They will then have 2 hours to click the link and update their password before it expires.</p>
<p>Only Staff users have permissions and can be changed as needed. Be careful if you are an administrator and remove the ''Admin Full Access'' permission as you will have to log in as the main system admin to change it. The main system admin account permissions cannot be changed.</p>
<p>Standard site Users do not have permissions and cannot edit pages. They are intended to be user who can have access to user only content pages. You can also export your user lists as a CSV and import them into your email sending applications for bulk newsletters and the like.</p>
<p>&nbsp;</p>
<p>&nbsp;</p>', '', ''),
  (3, 1, '<p>&nbsp;</p>
<p><strong>Javascript Example:</strong></p>
<p>Show the time:&nbsp;<span id="ct">time</span></p>
<p>&nbsp;</p>
<p><strong>CSS Example:</strong></p>
<p class="green-bg">This text should have a light green background</p>
<p>&nbsp;</p>
<p><strong>Image Gallery And Lightbox:</strong></p>
<p><img src="https://cdn.stocksnap.io/img-thumbs/960w/duck-bird_AIFJGUIFZJ.jpg" alt="" width="375" height="250"> &nbsp;<img src="https://cdn.stocksnap.io/img-thumbs/960w/duck-water_FPL1GGQR67.jpg" alt="" width="188" height="250"> <img src="https://cdn.stocksnap.io/img-thumbs/960w/duck-nature_8LZSGI5RRI.jpg" alt="" width="375" height="250"></p>
<p>&nbsp;</p>
<p><strong>Code markup using prisim.js plugin:</strong></p>
<pre class="language-php"><code>use Symfony\\Component\\Routing\Loader\\Configurator\\CollectionConfigurator;

return function (CollectionConfigurator $routes) {

    // Site public pages
    $routes-&gt;add(''wiki-contact'', ''/contact'')
        -&gt;controller([\\App\\Controller\\Contact::class, ''doDefault'']);
    $routes-&gt;add(''wiki-search'', ''/search'')
        -&gt;controller([\\App\\Controller\\Page\\Search::class, ''doDefault'']);

    // ...
};</code></pre>
<p>&nbsp;</p>
<p><strong>Videos:</strong></p>
<p><iframe style="width: 469px; height: 263px;" src="https://www.youtube.com/embed/dL0sleCI0cM" width="469" height="263" allowfullscreen="allowfullscreen"></iframe></p>
<p>&nbsp;</p>
<p>&nbsp;</p>', '.green-bg {
  background-color: #55FF55;
}', 'function display_c(){
var refresh=1000; // Refresh rate in milli seconds
mytime=setTimeout(''display_ct()'',refresh)
}

function display_ct() {
var x = new Date()
document.getElementById(''ct'').innerHTML = x;
display_c();
 }

jQuery(function ($) {

    display_ct();
});')
;

TRUNCATE menu_item;
INSERT INTO menu_item (parent_id, page_id, order_id, type, name) VALUES
  (null, 1, 1, 'item', 'Home'),
  (null, 2, 2, 'item', 'How To')
;

SET SQL_SAFE_UPDATES = 1;
SET FOREIGN_KEY_CHECKS = 1;




