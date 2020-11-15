Easy!Appointments
================

<img src="http://easyappointments.org/img/easyappointments-banner.png">

### Organize your business! Exploit human resources that can be used in other tasks more efficiently.

**Easy!Appointments** is a highly customizable web application that allows your customers to book
appointments with you via the web. Moreover, it provides the ability to sync your data with
Google Calendar so you can use them with other services. It is an open source project and you
can download and install it **even for commercial use**. Easy!Appointments will run smoothly with
your existing website, because it can be installed in a single folder of the server and of course,
both sites can share the same database.

### Features

The project was designed to be flexible and reliable so as to be able to meet the needs of any
kind of enterprise. You can read the main features of the system below:

* Full customers and appointments management.
* Services and service providers organization.
* Workflow and booking rules.
* Google Calendar synchronization.
* Email notifications system.
* Standalone installation (like WordPress, Drupal, Joomla and other web systems).
* Translated user interface.
* User community support.

### Installation

Since Easy!Appointments is a web application, it runs on a web server and thus you will need to
perform the following steps in order to install the system on your server:

* Make sure that your server has Apache/Nginx, PHP and MySQL installed.
* Create a new database (or use an existing).
* Copy the "easyappointments" source folder on your server.
* Make sure that the "storage" directory is writable.
* Rename the "config-sample.php" file to "config.php" and set your server properties.
* Open your browser on the Easy!Appointments URL and follow the installation guide.
* That's it! You can now use Easy!Appointments at your will.

You will find the latest release at [easyappointments.org](http://easyappointments.org).
If you have problems installing or configuring the application take a look on the
[wiki pages](https://github.com/alextselegidis/easyappointments/wiki) or visit the
[official support group](https://groups.google.com/forum/#!forum/easy-appointments).
You can also report problems on the [issues page](https://github.com/alextselegidis/easyappointments/issues)
and help the development progress.

### Docker
To start Easy!Appointments using Docker in development configuration, with source files mounted into container, run:
```
docker-compose up
```

Production deployment can be made by changing required values in .env file (DB_PASSWORD, APP_URL, APP_PORT) and running:
```
docker-compose -f docker-compose.prod.yml up -d
```

Database data will be stored in named volume `easyappointments_easy-appointments-data`, and app storage (logs, cache, uploads) in `easyappointments_easy-appointments-storage`.
To find where exactly they are stored, you can run 
```
docker volume inspect easyappointments_easy-appointments-storage
```

Production containers will automatically be restarted in case of crash / server reboot. For more info, take a look into `docker-compose.prod.yml` file.

### User Feedback

Whether it is new ideas or defects, your feedback is highly appreciated and will be taken into
consideration for the following releases of the project. Share your experience and discuss your
thoughts with other users through communities. Create issues with suggestions on new features or
bug reports.

### Translate Easy!Appointments

As of version 1.0 Easy!Appointments supports translated user interface. If you want to contribute to the
translation process read the [get involved](https://github.com/alextselegidis/easyappointments/blob/master/doc/get-involved.md)
page for additional information.
