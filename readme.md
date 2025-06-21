# Tk-Wiki

__Web:__ <https://github.com/tropotek/tkwiki>  
__Author:__ Tropotek <http://www.tropotek.com/>

A WIKI/CMS that is easy to use for people that want a website to store info fast.
Perfect for projects that require online documentation and can be edited by teams.

__Features:__
- The ability to add CSS and JavaScript within each page.
- Create/link pages within the WYSIWYG editor.
- Edit the nav menu within the wiki
- Bootstrap 5+ basic template
- Save 'Secret' site access details for the website and share them within groups
- QR-Code reader for generating One Time Password (OTP) for shared Secrets



## Contents

- [Installation](#installation)
- [Upgrading](#upgrading)

## Installation

- Set up a database for the site and keep the login details handy.
- Make sure you have the latest version of composer [https://getcomposer.org/download/].
- Use the following commands:
    ```bash
    $ git clone https://github.com/tropotek/tkwiki.git
    $ cd tkwiki
    $ composer install
    ```
- You will be asked a number of questions to set up the environment settings.
- Create a new admin user to log into the site:
    ```bash
    $ ./bin/cmd adm {username}
    ```
- Edit the `/config.php` file to your required settings.
- To enable debug mode and logging edit the `/config.php` file:
    ```php
        // Enable Debug in a dev environment
        $config['env.type'] = 'dev';
        // Setup dev environment
        if ($config->isDev()) {
            // Send all emails to the debug address
            $config['system.debug.email'] = 'dev@example.com';
            // allow any password without strict validation
            $config['auth.password.strict'] = false;
        }
    ```
- Change the permissions of the `/data/` folder to be writable by your server.
- Browse to the site URL and login with the admin user credentials.


## Upgrading

Upgrade the site by the CLI command;
```bash
$ cd {siteroot}
$ ./bin/cmd ug
```

Manual upgrade process if the above fails:
```bash
$ git reset --hard
$ git checkout 8.0.0    // Use the latest tag version here
$ composer update
```

__Warning:__ Upgrading could potentially break the site. Be sure to back up all DB's and
site `/data` files before running these commands.



