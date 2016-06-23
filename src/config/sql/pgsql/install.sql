



-- DROP TABLE IF EXISTS "user" CASCADE;
-- DROP TABLE IF EXISTS role CASCADE;
-- DROP TABLE IF EXISTS user_permission CASCADE;
-- DROP TABLE IF EXISTS user_role CASCADE;
-- DROP TABLE IF EXISTS data CASCADE;
-- DROP TABLE IF EXISTS page CASCADE;
-- DROP TABLE IF EXISTS content CASCADE;
-- DROP TABLE IF EXISTS pagelink CASCADE;
-- DROP TABLE IF EXISTS lock CASCADE;
-- DROP TABLE IF EXISTS version CASCADE;

-- --------------------------------------------------------


-- --------------------------------------------------------
-- Table structure for table `user`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS "user" (
  id SERIAL PRIMARY KEY,
  name VARCHAR(128),
  email VARCHAR(255),
  image VARCHAR(255),
  username VARCHAR(64),
  password VARCHAR(64),
  active BOOLEAN,
  hash VARCHAR(64),
  last_login TIMESTAMP,
  modified TIMESTAMP DEFAULT NOW(),
  created TIMESTAMP DEFAULT NOW(),
  CONSTRAINT username UNIQUE (username),
  CONSTRAINT email UNIQUE (email),
  CONSTRAINT "hash" UNIQUE (hash)
);

-- ---------------------------------------------------------
-- User roles/permissions, not related to page permissions
-- The role permissions superseeds page permissions
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS role (
  id SERIAL PRIMARY KEY,
  name VARCHAR(128) NOT NULL,
  description TEXT,
  CONSTRAINT name UNIQUE (name)
);

CREATE TABLE IF NOT EXISTS user_role (
  user_id INTEGER NOT NULL,
  role_id INTEGER NOT NULL,
  FOREIGN KEY (user_id) REFERENCES "user"(id) ON DELETE CASCADE,
  FOREIGN KEY (role_id) REFERENCES role(id) ON DELETE CASCADE
);


-- --------------------------------------------------------
-- Table structure for table `data`
-- This is the replacement for the `settings` table
-- Use foreign_id = 0 and foreign_key = `system` for site settings (suggestion only)
-- Can be used for other object data using the foreign_id and foreign_key
-- foreign_key can be a class namespace or anything describing the data group
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS data (
  id SERIAL PRIMARY KEY,
  foreign_id INTEGER NOT NULL DEFAULT 0,
  foreign_key VARCHAR(128) NOT NULL DEFAULT '',
  key VARCHAR(255),
  value TEXT,
  CONSTRAINT foreign_fields UNIQUE (foreign_id, foreign_key, key)
);

-- --------------------------------------------------------
-- Table structure for table `page`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS page (
  id SERIAL PRIMARY KEY,
  user_id INTEGER NOT NULL DEFAULT 0,             -- The author of the page
  type VARCHAR(64) NOT NULL DEFAULT 'page',       -- The page type: `page`, `nav`, etc...
  template varchar(255) NOT NULL DEFAULT '',      -- use a different page template if selected
  title VARCHAR(128) NOT NULL DEFAULT '',
  url VARCHAR(128) NOT NULL DEFAULT '',           -- the base url of the page
  views INTEGER NOT NULL DEFAULT 0,               -- Page views per (1 per session)
  modified TIMESTAMP DEFAULT NOW(),
  created TIMESTAMP DEFAULT NOW(),
  CONSTRAINT url UNIQUE (url),
  FOREIGN KEY (user_id) REFERENCES "user"(id)
);

-- --------------------------------------------------------
-- Table structure for table `content`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS content (
  id SERIAL PRIMARY KEY,
  page_id INTEGER NOT NULL DEFAULT 0,             -- The parent page
  user_id INTEGER NOT NULL DEFAULT 0,             -- The author of the updated content
  
  html TEXT,
  keywords VARCHAR(255) NOT NULL DEFAULT '',      -- adds to the global meta keywords
  description VARCHAR(255) NOT NULL DEFAULT '',   -- adds to the global meta description  
  css TEXT,
  js TEXT,
  size INTEGER NOT NULL DEFAULT 0,                -- Page content size in bytes

  modified TIMESTAMP DEFAULT NOW(),
  created TIMESTAMP DEFAULT NOW(),
  FOREIGN KEY (page_id) REFERENCES page(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES "user"(id)
);


-- --------------------------------------------------------
-- Table structure for table `pagelink`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS pagelink (
  page_from INTEGER NOT NULL DEFAULT '0',
  page_to_url VARCHAR(255) NOT NULL DEFAULT '',
  CONSTRAINT page_from_to UNIQUE (page_from, page_to_url),
  FOREIGN KEY (page_from) REFERENCES page(id) ON DELETE CASCADE
);
-- proposed new pagelink table
-- CREATE TABLE IF NOT EXISTS pagelink (
--   page_from_id INTEGER NOT NULL DEFAULT '0',
--   page_to_id INTEGER NOT NULL DEFAULT '0',
--   CONSTRAINT page UNIQUE (page_from_id, page_to_id),
--   FOREIGN KEY (page_from_id) REFERENCES page(id),
--   FOREIGN KEY (page_to_id) REFERENCES page(id)
-- );


-- --------------------------------------------------------
-- Table structure for table `lock`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS lock (
  hash VARCHAR(64) NOT NULL DEFAULT '' PRIMARY KEY,
  page_id INTEGER NOT NULL DEFAULT 0,
  user_id INTEGER NOT NULL DEFAULT 0,
  ip VARCHAR(128) NOT NULL DEFAULT '',
  expire TIMESTAMP NOT NULL,
  FOREIGN KEY (page_id) REFERENCES page(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES "user"(id) ON DELETE CASCADE
);


-- --------------------------------------------------------
-- Table structure for table `version`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS version (
  id SERIAL PRIMARY KEY,
  version VARCHAR(32) NOT NULL DEFAULT '1.0.0',
  changelog TEXT,
  modified TIMESTAMP DEFAULT NOW(),
  created TIMESTAMP DEFAULT NOW(),
  CONSTRAINT version_str UNIQUE (version)
);


