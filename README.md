php-fpm-munin-plugins
======================

History
-------
This codebase was originally forked from [leprechau/php-fpm-munin-plugins](https://github.com/leprechau/php-fpm-munin-plugins).

It's a modification so that you can use urls in the munin config to easily let it connect to different pools. 

Installation on generic Linux
-----------------------------

    cd /usr/share/munin/plugins
    git clone git://github.com/PauluzzNL/php-fpm-munin-plugins.git
    chmod +x php-fpm-munin-plugins/phpfpm_check

    # General
    ln -s /usr/share/munin/plugins/php-fpm-munin-plugins/phpfpm_check /etc/munin/plugins/phpfpm_average
    ln -s /usr/share/munin/plugins/php-fpm-munin-plugins/phpfpm_check /etc/munin/plugins/phpfpm_memory

    # Domain Specific
    ln -s /usr/share/munin/plugins/php-fpm-munin-plugins/phpfpm_check /etc/munin/plugins/phpfpm_connection__domain.tld
    ln -s /usr/share/munin/plugins/php-fpm-munin-plugins/phpfpm_check /etc/munin/plugins/phpfpm_process__domain.tld
    ln -s /usr/share/munin/plugins/php-fpm-munin-plugins/phpfpm_check /etc/munin/plugins/phpfpm_status__domain.tld
    service munin-node restart

You will need the /status url enabled for all plugins (except phpfpm_average and phpfpm_memory).  To enable this functionality please uncomment and set the 'pm.status_path' option in your php-fpm config.  The phpfpm_average and phpfpm_memory plugins have only been tested on Linux, FreeBSD, OpenBSD, NetBSD and Mac OSX.  You will need to edit the switch in the '_check_osname' function contained within the 'common.php' file.

Jérôme Loyet from the Nginx forums provided some useful insight on how to get this working with Nginx.

    location ~ ^/(status|ping)$ {
        include fastcgi_params;
        fastcgi_pass backend;
        fastcgi_param SCRIPT_FILENAME $fastcgi_script_name;
        allow 127.0.0.1:9000;
        allow stats_collector.localdomain;
        allow watchdog.localdomain;
        deny all;
    }

You should confirm that you can successfully return the contents of http://127.0.0.1/status?json with curl on your local system:

    curl "http://127.0.0.1/status?json"

You should see something similar to the following:

    root@localhost:/root # curl "http://127.0.0.1/status?json"
    {"pool":"www","process manager":"dynamic","start time":1376428225,"start since":7707,"accepted conn":32,"listen queue":0,"max listen queue":0,"listen queue len":128,"idle processes":1,"active processes":1,"total processes":2,"max active processes":1,"max children reached":0,"slow requests":0}

With apache / apache plesk you should ensure the status URL is also proxied to the php-fpm service/socket. A quick method is to use a php path as status_path. For example fpm.status.php - this will set you up without any additional hassle. 

Environment variables
---------------------

* env.url: Set the url for the php-fpm status check, defaults to: _http://127.0.0.1:80/status?json_
* env.fpmbin: Set the name of your php-fpm binary, defaults to: _php-fpm_

Requirements
------------

You will need php 5.3.2+ with json and curl support to take advantage of all plugins.
