# tkwiki

__Project:__ tkwiki    
__Web:__ <https://github.com/tropotek/tkwiki>  
__Authors:__ Tropotek <http://www.tropotek.com/>

A WIKI/CMS that is easy to use for people that want a website to store info fast.
Perfect for projects that require online documentation, and can be edited by teams.

__Features:__
- The ability to add CSS and Javascript within each page.
- Create/link pages within the WYSIWYG editor.
- Edit the top nav menu
- All templates are based on Bootstrap 5+
- 

## Contents

- [Installation](#installation)
- [Upgrading](#upgrading)

## Installation

1. Before installing, set up a database for the site and keep the DB name and login details handy.
2. Make sure you have the latest version of composer [https://getcomposer.org/download/].
3. Use the following commands:
    ```bash
    $ git clone https://github.com/tropotek/tkwiki.git
    $ cd tkwiki
    $ composer install
    ```
4. You will be asked a number of questions to set up the environment settings.
5. Edit the `/config.php` file to your required settings.
6. Check the permissions of the site `/data/` folder so PHP can read and write to it.
8. Browse to the URI that was shown at the end of install process to see if it all worked.
9. To log in with the default `wikiadmin` account, you will need to create a password. 
To create the admin account password execute the password command using the site's CLI tool:
    ```bash
    $ ./bin/cmd pwd wikiadmin
    ```


## Upgrading


Upgrade the site using the CLI command, whis will :
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

__Warning:__ Upgrading could potentially break the site change the database. Remember to back up any DB and
site files before running these commands.


