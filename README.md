dyn-login
=========

A quick and dirty script to automatically log in on dyn.com in order to keep a free account active

Note : As of may 2014, Dyn.com end their free hostname program. Time to put some money up or find another solution ;)

Requirements
------------
* PHP
* curl extension

Installation
------------
* Get the source code (git clone, zip archive, whatever)
* Copy `conf.dist.php` to `conf.php` and fill in your dyn.com credentials
* Call script: `php dynlogin.php`

To automate the process, you can then add the script in a crontab:
```
# Every sunday at 5 AM
0 5 * * 0 php /path/to/dyn-login/dynlogin.php
```

