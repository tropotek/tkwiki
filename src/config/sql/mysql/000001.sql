
TRUNCATE `user`;
INSERT INTO `user` (`id`, `name`, `email`, `username`, `password`, `role`, `active`, `hash`, `modified`, `created`)
VALUES
  (NULL, 'Administrator', 'admin@example.com', 'admin', MD5('password'), 'admin', 1, MD5('1admin'), NOW() , NOW())
#   ,(NULL, 'User 1', 'user@example.com', 'user1', MD5('password'), 'user', 1, MD5('2user1'), NOW() , NOW())
;


TRUNCATE `role`;
INSERT INTO `role` (`name`, `description`) VALUES
  ('admin', 'Manage site, groups, users, pages, etc no restrictions'),
  ('moderator', 'Manage assigned users and pages for assigned groups'),
  ('user', 'Manage user settings, pages only'),
  ('create', 'Create pages'),
  ('edit', 'Edit existing pages'),
  ('delete', 'Delete pages'),
  ('editExtra', 'Can edit page css, js, url and template options');

TRUNCATE `user_role`;
INSERT INTO user_role (user_id, role_id)
VALUES
  -- Administrator
  (1, 1), (1, 2), (1, 3), (1, 4), (1, 5), (1, 6), (1, 7), (1, 8)
  -- Moderator
--  ,(2, 2), (2, 3), (2, 4), (2, 5), (2, 6), (2, 7), (2, 8),
  -- User
--  (3, 3), (3, 4), (3, 5), (3, 6), (3, 7), (3, 8)
;

-- Add the home page to the site This should be non-deletable
TRUNCATE `page`;
INSERT INTO `page` (user_id, type, template, title, url, modified, created) VALUES
  (1, 'page', '', 'Home', 'Home', NOW(), NOW())
;

TRUNCATE `content`;
INSERT INTO content (page_id, user_id, html, modified, created) VALUES
  (1, 1, '<p>This blog post shows a few different types of content that''s supported and styled with Bootstrap. Basic
         typography, images, and code are all supported.</p>
      <p>Cum sociis natoque penatibus et magnis <a href="#">dis parturient montes</a>, nascetur ridiculus mus. Aenean eu
         leo quam. Pellentesque ornare sem lacinia quam venenatis vestibulum. Sed posuere consectetur est at lobortis.
         Cras mattis consectetur purus sit amet fermentum.</p>
      <blockquote>
        <p>Curabitur blandit tempus porttitor. <strong>Nullam quis risus eget urna mollis</strong> ornare vel eu leo.
           Nullam id dolor id nibh ultricies vehicula ut id elit.</p>
      </blockquote>
      <p>Etiam porta <em>sem malesuada magna</em> mollis euismod. Cras mattis consectetur purus sit amet fermentum.
         Aenean lacinia bibendum nulla sed consectetur.</p>
      <h2>Heading</h2>
      <p>Vivamus sagittis lacus vel augue laoreet rutrum faucibus dolor auctor. Duis mollis, est non commodo luctus,
         nisi erat porttitor ligula, eget lacinia odio sem nec elit. Morbi leo risus, porta ac consectetur ac,
         vestibulum at eros.</p>
      <h3>Sub-heading</h3>
      <p>Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.</p>
      <pre><code>Example code block</code></pre>
      <p>Aenean lacinia bibendum nulla sed consectetur. Etiam porta sem malesuada magna mollis euismod. Fusce dapibus,
         tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa.</p>
      <h3>Sub-heading</h3>
      <p>Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Aenean lacinia bibendum
         nulla sed consectetur. Etiam porta sem malesuada magna mollis euismod. Fusce dapibus, tellus ac cursus commodo,
         tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus.</p>
      <ul>
        <li>Praesent commodo cursus magna, vel scelerisque nisl consectetur et.</li>
        <li>Donec id elit non mi porta gravida at eget metus.</li>
        <li>Nulla vitae elit libero, a pharetra augue.</li>
      </ul>
      <p>Donec ullamcorper nulla non metus auctor fringilla. Nulla vitae elit libero, a pharetra augue.</p>
      <ol>
        <li>Vestibulum id ligula porta felis euismod semper.</li>
        <li>Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.</li>
        <li>Maecenas sed diam eget risus varius blandit sit amet non magna.</li>
      </ol>
      <p>Cras mattis consectetur purus sit amet fermentum. Sed posuere consectetur est at lobortis.</p>

      <h1 class="blog-post-title">Sample blog post</h1>
      <h2>Heading</h2>
      <p>Vivamus sagittis lacus vel augue laoreet rutrum faucibus dolor auctor. Duis mollis, est non commodo luctus,
         nisi erat porttitor ligula, eget lacinia odio sem nec elit. Morbi leo risus, porta ac consectetur ac,
         vestibulum at eros.</p>
      <h3>Sub-heading</h3>
      <p>Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.</p>
      <pre><code>Example code block</code></pre>
      <p>Aenean lacinia bibendum nulla sed consectetur. Etiam porta sem malesuada magna mollis euismod. Fusce dapibus,
         tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa.</p>
      <h3>Sub-heading</h3>
      <p>Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.</p>', NOW(), NOW())
;




