# tkwiki

__Project:__ tkwiki    
__Web:__ <https://github.com/tropotek/tkwiki>  
__Authors:__ Tropotek <http://www.tropotek.com/>

A WIKI/CMS that is easy to use for people that want a website to store info fast.
Great for projects that need documentation, can be edited live using WYSIWYG editors within the site.

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

1. Befor installing, setup a database for the site and keep the DB name and login details handy.
2. Make sure you have the latest version of composer [https://getcomposer.org/download/].
3. Use the following commands:
```bash
$ git clone https://github.com/tropotek/tkwiki.git
$ cd tkwiki
$ composer install
```
4. You will be asked a number of questions to setup the environment settings.
5. Edit the `/src/App/config/config.php` file to your required settings.
6. You may have to change the permissions of the `/data/` folder so PHP can read and write to it.
7. To enable debug mode and logging edit the `/src/config/config.php` file to suit your server.
8. Browse to the location that was shown at the end of install to see if it all worked.
9. You should be able to login with the default admin (P: password) account. Be sure to change 
that before release. From the command line in the site base path you can call, or in the user account profile page in the site:
```bash
$ ./bin/cmd password admin <password>
```


## Upgrading

__NOTICE:__ Wiki Ver 8.0+ is based on a completely re-written base library. So there is no upgrade scripts from
previous major versions. You will have to manually copy content and media.

```bash
$ git reset --hard
$ git checkout master
$ git pull
$ composer update
```

__Warning:__ Upgrading could potentially break the site change the database. Back up any DB and
site files before running these commands.







