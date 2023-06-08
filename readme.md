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
- [Introduction](#introduction)

## Installation

1. First setup a database for the site and keep the DB name and login details handy.
2. Make sure you have the latest version of composer [https://getcomposer.org/download/] installed.
3. Use the following commands:
~~~bash
$ git clone https://github.com/tropotek/tkwiki.git
$ cd tkwiki
$ composer install
~~~
5. You will be asked a number of questions to setup the environment settings.
4. Edit the `/src/App/config/config.php` file to your required settings.
5. You may have to change the permissions of the `/data/` folder so PHP can read and write to it.
6. To enable debug mode and logging edit the `/src/config/config.php` file to suit your server.
   (tail the log for more info on any errors.)
7. Browse to the location that was shown at the end of install to see if it all worked.


## Upgrading

__Warning:__ Ver 8.0+ is based on a completely re-written base library. So there is no upgrade scripts from
previous versions. There is a page import system, but you will need to have both the old and new sites live
and add a secret key to both sites, only admins will be able to import pages.

## Introduction






