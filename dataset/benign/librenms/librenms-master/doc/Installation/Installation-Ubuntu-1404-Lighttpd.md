source: Installation/Installation-Ubuntu-1404-Lighttpd.md

path: blob/master/doc/
> NOTE: These instructions assume you are the **root** user.  If you
are not, prepend `sudo` to the shell commands (the ones that aren't at
`mysql>` prompts) or temporarily become a user with root privileges
with `sudo -s` or `sudo -i`.

**Please note the minimum supported PHP version is 5.6.4**

# On the DB Server

This host is where the MySQL database runs.  It could be the same
machine as your network management server (this is the most common
initial deployment scenario).

> ** Whilst we are working on ensuring LibreNMS is compatible with
> MySQL strict mode, for now, please disable this after mysql is
> installed.

You are free to choose between using MySQL or MariaDB:

## MySQL

```bash
apt-get install mysql-server mysql-client
mysql -uroot -p
```

## MariaDN

```bash
apt-get install mariadb-server mariadb-client
mysql -uroot -p
```

## General

Enter the MySQL root password to enter the MySQL command-line interface.

Create database.

```sql
CREATE DATABASE librenms CHARACTER SET utf8 COLLATE utf8_unicode_ci;
CREATE USER 'librenms'@'localhost' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON librenms.* TO 'librenms'@'localhost';
FLUSH PRIVILEGES;
exit
```

Replace `<ip>` above with the IP of the server running LibreNMS.  If
your database is on the same server as LibreNMS, you can just use
`localhost` as the IP address.

If you are deploying a separate database server, you need to change
the `bind-address`.  If your MySQL database resides on the same server
as LibreNMS, you should skip this step.

```
vim /etc/mysql/my.cnf
```

Within the [mysqld] section please add:

```
    innodb_file_per_table=1
```

Find the line: `bind-address = 127.0.0.1`

Change `127.0.0.1` to the IP address that your MySQL server should
listen on.  Restart MySQL:

If you see a line that starts `sql-mode` then change this to `sql-mode=""`.

```
service mysql restart
```

# On the NMS

Install necessary software.  The packages listed below are an
all-inclusive list of packages that were necessary on a clean install
of Debian 7.

```
apt-get install lighttpd php5-cli php5-mysql php5-gd php5-snmp php5-cgi php-pear php5-curl snmp graphviz rrdtool sendmail fping imagemagick whois mtr-tiny nmap ipmitool php5-mcrypt php5-json python-mysqldb snmpd php-net-ipv4 php-net-ipv6 rrdtool git
```

# Adding the librenms-user

```
useradd librenms -d /opt/librenms -M -r
usermod -a -G librenms www-data
```

# Cloning

You can clone the repository via HTTPS or SSH.  In either case, you
need to ensure the appropriate port (443 for HTTPS, 22 for SSH) is
open in the outbound direction for your server.

```
cd /opt
git clone https://github.com/librenms/librenms.git librenms
cd /opt/librenms
```

At this stage you can either launch the web installer by going to
<http://IP/install.php>, follow the onscreen instructions then skip to
the 'Web Interface' section further down. Alternatively if you want to
continue the setup manually then just keep following these
instructions.

```
cp config.php.default config.php
vim config.php
```

> NOTE: The recommended method of cloning a git repository is HTTPS.
> If you would like to clone via SSH instead, use the command `git
> clone git@github.com:librenms/librenms.git librenms` instead.

Change the values to the right of the equal sign for lines beginning
with `$config[db_]` to match your database information as setup above.

Change the value of `$config['snmp']['community']` from `public` to
whatever your read-only SNMP community is.  If you have multiple
communities, set it to the most common.

** Be sure you have no characters (including whitespace like:
newlines, spaces, tabs, etc) outside of the `<?php?>` blocks. Your
graphs will break otherwise. **

# Initialise the database

Initiate the follow database with the following command:

```
php build-base.php
```

# Create admin user

Create the admin user - priv should be 10

```
php adduser.php <name> <pass> 10 <email>
```

Substitute your desired username, password and email address--and
leave the angled brackets off.

# Web Interface

To prepare the web interface (and adding devices shortly), you'll need
to set up Lighttpd.

First, create and chown the `rrd` directory and create the `logs` directory

```
mkdir rrd logs
chown -R librenms:librenms /opt/librenms
chmod 775 rrd
```

> NOTE: If you're planing on running rrdcached, make sure that the
> path is also chmod'ed to 775 and chown'ed to librenms:librenms.

Next, add the following to `/etc/lighttpd/librenms.conf`

```
     server.document-root = "/opt/librenms/html"
     url.rewrite-once = (
       "^/(.*)\.(gif|jpg|jpeg|tiff|svg|png|css|php)$" => "/$0",
       "^/([a-z|0-9\-]+)/(.+)/(.+)/(.+)/(.+)/(.+)/(.+)/(.+)/(.+)/(.+)/(.+)/" => "/?page=$1&$2&$3&$4&$5&$6&$7&$8&$9&$10",
       "^/([a-z|0-9\-]+)/(.+)/(.+)/(.+)/(.+)/(.+)/(.+)/(.+)/(.+)/(.+)/" => "/?page=$1&$2&$3&$4&$5&$6&$7&$8&$9",
       "^/([a-z|0-9\-]+)/(.+)/(.+)/(.+)/(.+)/(.+)/(.+)/(.+)/(.+)/" => "/?page=$1&$2&$3&$4&$5&$6&$7&$8",
       "^/([a-z|0-9\-]+)/(.+)/(.+)/(.+)/(.+)/(.+)/(.+)/(.+)/" => "/?page=$1&$2&$3&$4&$5&$6&$7",
       "^/([a-z|0-9\-]+)/(.+)/(.+)/(.+)/(.+)/(.+)/(.+)/" => "/?page=$1&$2&$3&$4&$5&$6",
       "^/([a-z|0-9\-]+)/(.+)/(.+)/(.+)/(.+)/" => "/?page=$1&$2&$3&$4&$5",
       "^/([a-z|0-9\-]+)/(.+)/(.+)/(.+)/" => "/?page=$1&$2&$3&$4",
       "^/([a-z|0-9\-]+)/(.+)/(.+)/" => "/?page=$1&$2&$3",
       "^/([a-z|0-9\-]+)/(.+)/" => "/?page=$1&$2",
       "^/([a-z|0-9\-]+)/$" => "/?page=$1"
     )
```

Next, add the following to `/etc/lighttpd/lighttpd.conf`

```
     $HTTP["host"] == "example.com" { include "librenms.conf" }
```

And enable mod_rewrite by uncommenting

```
#       "mod_rewrite",
```

to

```
"mod_rewrite",
```

Don't forget to change 'example.com' to your domain

Enable fastcgi in Lighttpd by running the following commands,

```
lighty-enable-mod fastcgi
lighty-enable-mod fastcgi-php
```

And add the following to /etc/php5/cgi/php.ini

```
cgi.fix_pathinfo = 1
```

then restart Lighttpd:

```
service lighttpd restart
```

# Validate your install

Run validate.php as root in the librenms directory

```
php validate.php
```

This will check your install to verify it is set up correctly.

# Add localhost

```
php addhost.php localhost public v2c
```

This assumes you haven't made community changes--if you have, replace
`public` with your community.  It also assumes SNMP v2c.  If you're
using v3, there are additional steps (NOTE: instructions for SNMPv3 to
come).

Discover localhost:

```
php discovery.php -h all
```

# Create cronjob

The polling method used by LibreNMS is `poller-wrapper.py`, which was
placed in the public domain by its author.  By default, the LibreNMS
cronjob runs `poller-wrapper.py` with 16 threads.  The current
LibreNMS recommendation is to use 4 threads per core.  The default if
no thread count is `16 threads`.

If the thread count needs to be changed, you can do so by editing the
cron file (`/etc/cron.d/librenms`). Just add a number after
`poller-wrapper.py`, as in the below example:

```
/opt/librenms/poller-wrapper.py 12 >> /dev/null 2>&1
```

Create the cronjob

```
cp librenms.nonroot.cron /etc/cron.d/librenms
```

> NOTE: Keep in mind  that cron, by default, only uses a very limited
> set of environment variables. You may need to configure proxy
> variables for the cron invocation. Alternatively adding the proxy
> settings in config.php is possible too. The config.php file will be
> created in the upcoming steps. Review the following URL after you
> finished librenms install steps:
> <https://docs.librenms.org/Support/Configuration/#proxy-support>

# Copy logrotate config

LibreNMS keeps logs in `/opt/librenms/logs`. Over time these can
become large and be rotated out.  To rotate out the old logs you can
use the provided logrotate config file:

```
cp misc/librenms.logrotate /etc/logrotate.d/librenms
```

# Daily Updates

LibreNMS performs daily updates by default.  At 00:15 system time
every day, a `git pull --no-edit --quiet` is performed.  You can
override this default by editing your `config.php` file.  Remove the
comment (the `#` mark) on the line:

```
#$config['update'] = 0;
```

so that it looks like this:

```
$config['update'] = 0;
```

# Install complete

Please allow for 2-3 runs of the poller-wrapper for data to start
appearing in the WebUI. If you don't see data after this, please refer
to the [FAQ](http://docs.librenms.org/Support/FAQ/) for assistance.

That's it!  You now should be able to log in to
<http://librenms.example.com/>.  Please note that we have not covered
HTTPS setup in this example, so your LibreNMS install is not secure by
default. Please do not expose it to the public Internet unless you
have configured HTTPS and taken appropriate web server hardening
steps.

It would be great if you would consider opting into the stats system
we have, please see [this
page](http://docs.librenms.org/General/Callback-Stats-and-Privacy/) on
what it is and how to enable it.

