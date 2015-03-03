
                       README - DkWiki
                     www.domtemplate.com

DkWiki  Copyright (C) 2008  Michael Mifsud <http://www.domtemplate.com/>
This program comes with ABSOLUTELY NO WARRANTY.
This is free software, and you are welcome to redistribute it
under certain conditions.

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation <http://www.gnu.org/licenses/>.
-----------------------------------------------------


##NOTE: OLD INSTALL DOCS##


REQUIREMENTS:

 - Linux
 - Apache v1+ (v2 Tested)
 - MySQL v4+ (v5 Tested)
 - PHP v5

PREPERATION:

 1. Before any upgrade be sure to backup your existing DkWiki
    site files. This will guard you against any unforseen errors

 2. If you are installing for the first time you will need to create
    a new .htacess file from the example one provided

        # cd {DkWiki_Path}
        # cp .htaccess.in .htccess

    If your DkWiki is located in the root of the domain then thats all you need.
    However if you have installed the DkWiki into a subdirectory you will need
    update the following line, be sure to add the ending forward slash:

        # vi .htaccess
           RewriteBase /    # Default rewrite path
           OR
           RewriteBase /{DkWiki-htdoc-path}/    # Custom URL path

    More Info: http://httpd.apache.org/docs/2.0/mod/mod_rewrite.html#RewriteBase

 3. Lastly we need read/write permissions to the data directory.
    This is where the application will store all cache files and user
    files. For advanced users you can modify the .htaccess in teh data
    directory to restrict files for added security.

        # mkdir data
        # chmod -R ugo+rw data/


MANUAL INSTALLATION:

 1. Copy the config.ini.in to config.ini and change the configuration options
    to your required settings. You will need to add you DB settings here.

 4. Have a look in the '/DkWiki/lib/Wik/sql' directory and you will need to install
    the install.sql and any install-data.sql file. This is not covered here see the MySQL docs <http://dev.mysql.com/doc/>
    for how this is done for your particular server. If you are upgrading a previous install see the
    UPGRADE section.
    For linux command line:

    # mysql DkWiki -u username -ppassword < lib/Wik/sql/install.sql
    # mysql DkWiki -u username -ppassword < lib/Wik/sql/install-data.sql



UPGRADE:

 1. copy the new files over the old site. Also check the config.ini.in files for any additions you
    may want to add to your live config file.

 2. Use the upgrade sql files in /lib/Wik/sql/upgrade-???.sql
    You can run all the upgrade files after the version you have installed, so If you have v1.0 you
    would need to run all upgrades above an up to the new version.
