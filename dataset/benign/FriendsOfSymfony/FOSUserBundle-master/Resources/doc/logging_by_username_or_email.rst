Logging in by Username or Email
===============================

As of the version 1.3.0, the bundle provides a built-in user provider implementation
using both the username and email fields. To use it, simply change the id
of your user provider to use this implementation instead of the base one
using only the username:

.. code-block:: yaml

    # app/config/security.yml
    security:
        providers:
            fos_userbundle:
                id: fos_user.user_provider.username_email
