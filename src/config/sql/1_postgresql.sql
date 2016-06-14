



DROP TABLE IF EXISTS "user";
CREATE TABLE IF NOT EXISTS "user" (
  id SERIAL PRIMARY KEY,
  name VARCHAR(64),
  email VARCHAR(64),
  username VARCHAR(64),
  password VARCHAR(64),
  role TEXT,
  active BOOLEAN,
  hash VARCHAR(64),
  modified TIMESTAMP DEFAULT NOW(),
  created TIMESTAMP DEFAULT NOW(),
  CONSTRAINT username UNIQUE (username),
  CONSTRAINT email UNIQUE (email)
);

INSERT INTO "user" (id, name, email, username, password, role, active, hash, modified, created)
VALUES
  (1, 'Administrator', 'admin@example.com', 'admin', MD5('password'), 'admin', true, MD5('1:admin:admin@example.com'), NOW() , NOW()),
  (2, 'User 1', 'user@example.com', 'user1', MD5('password'), 'user', true, MD5('2:user:user@example.com'), NOW() , NOW())
;


