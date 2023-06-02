-- --------------------------------------------
-- @version 8.0.0 install
-- --------------------------------------------

CREATE TABLE IF NOT EXISTS user
(
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  uid VARCHAR(128) NOT NULL DEFAULT '',
  type VARCHAR(32) NOT NULL DEFAULT '',
  permissions BIGINT NOT NULL DEFAULT 0,
  username VARCHAR(128) NULL,
  password VARCHAR(128) NOT NULL DEFAULT '',
  email VARCHAR(255) NOT NULL DEFAULT '',
  name VARCHAR(128) NOT NULL DEFAULT '',
  image VARCHAR(255) NOT NULL DEFAULT '',
  notes TEXT DEFAULT '',
  timezone VARCHAR(64) NULL,
  active BOOL NOT NULL DEFAULT TRUE,
  hash VARCHAR(64) NOT NULL DEFAULT '',
  session_id VARCHAR(128) NOT NULL DEFAULT '',
  last_login TIMESTAMP NULL,
  modified TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uk_username (username),
  KEY k_uid (uid),
  KEY k_type (type)
);

-- User tokens to enable the 'Remember Me' functionality
CREATE TABLE IF NOT EXISTS user_tokens
(
  id INT AUTO_INCREMENT PRIMARY KEY,
  selector VARCHAR(255) NOT NULL,
  hashed_validator VARCHAR(255) NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  expiry DATETIME NOT NULL,
  CONSTRAINT fk_user_tokens__user_id FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE
);

-- A page container
CREATE TABLE IF NOT EXISTS page
(
  id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT(11) UNSIGNED NULL,
  type VARCHAR(64) NOT NULL DEFAULT 'page',
  title VARCHAR(255) NOT NULL DEFAULT '',
  url VARCHAR(255) NULL,
  views INT(11) UNSIGNED NOT NULL DEFAULT 0,
  permission INT NOT NULL DEFAULT 0,
  published BOOL NOT NULL DEFAULT TRUE,
  modified TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uk_url (url),
  KEY user_id (user_id),
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
  modified TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
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


-- Site default content

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_SAFE_UPDATES = 0;

TRUNCATE TABLE user;
INSERT INTO user (type, username, email, name, timezone, permissions) VALUES
  ('staff', 'admin', 'admin@example.com', 'Administrator', NULL, 1),
  ('staff', 'editor', 'dev@example.com', 'Developer', 'Australia/Melbourne', 1),
  ('user', 'user', 'user@example.com', 'User', 'Australia/Brisbane', 0)
;

UPDATE `user` SET `hash` = MD5(CONCAT(username, id)) WHERE 1;

INSERT INTO page (user_id, type, title, url, permission) VALUES
    (1, 'page', 'Home', 'home', 0),
    (1, 'nav', 'Menu', NULL, 0)
;
INSERT INTO content (page_id, user_id, html) VALUES
    (1, 1, '<h2>Welcome to the WIKI</h2>
<p>This is the default homepage of you new WIKI. Start adding content and building your own content.</p>
<p>&nbsp;</p>
<p><small>TODO: Add some sturtup content howto`s, introduction etc....</small></p>
<p>&nbsp;</p>'),
    (2, 1, '<ul>
<li><a href="#">Item 1</a></li>
<li><a href="#">Item 2</a></li>
<li><a href="#">Item 3</a></li>
<li><a href="#">Item 4</a></li>
</ul>')
;

SET SQL_SAFE_UPDATES = 1;
SET FOREIGN_KEY_CHECKS = 1;



