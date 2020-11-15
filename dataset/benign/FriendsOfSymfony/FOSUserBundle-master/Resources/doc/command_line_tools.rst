FOSUserBundle Command Line Tools
================================

The FOSUserBundle provides a number of command line utilities to help manage your
application's users. Commands are available for the following tasks:

1. Create a User
2. Activate a User
3. Deactivate a User
4. Promote a User
5. Demote a User
6. Change a User's Password

.. note::

    You must have correctly installed and configured the FOSUserBundle before
    using these commands.

.. note::

    This documentation references the console as ``bin/console``, which is
    the Symfony 3 location. If you use Symfony 2.x, use ``app/console`` instead.

Create a User
-------------

You can use the ``fos:user:create`` command to create a new user for your application.
The command takes three arguments, the ``username``, ``email``, and ``password`` for
the user you are creating.

For example if you wanted to create a user with username ``testuser``, with email
``test@example.com`` and password ``p@ssword``, you would run the command as follows.

.. code-block:: bash

    $ php bin/console fos:user:create testuser test@example.com p@ssword

If any of the required arguments are not passed to the command, an interactive prompt
will ask you to enter them. For example, if you ran the command as follows, then
you would be prompted to enter the ``email`` and ``password`` for the user
you want to create.

.. code-block:: bash

    $ php bin/console fos:user:create testuser

There are two options that you can pass to the command as well. They are
``--super-admin`` and ``--inactive``.

Specifying the ``--super-admin`` option will flag the user as a super admin when
the user is created. A super admin has access to any part of your application.
An example is provided below:

.. code-block:: bash

    $ php bin/console fos:user:create adminuser --super-admin

If you specify the ``--inactive`` option, then the user that you create will no be
able to log in until he is activated.

.. code-block:: bash

    $ php bin/console fos:user:create testuser --inactive

Activate a User
---------------

The ``fos:user:activate`` command activates an inactive user. The only argument
that the command requires is the ``username`` of the user who should be activated.
If no ``username`` is specified then an interactive prompt will ask you
to enter one. An example of using this command is listed below.

.. code-block:: bash

    $ php bin/console fos:user:activate testuser

Deactivate a User
-----------------

The ``fos:user:deactivate`` command deactivates a user. Just like the activate
command, the only required argument is the ``username`` of the user who should be
activated. If no ``username`` is specified then an interactive prompt will ask you
to enter one. Below is an example of using this command.

.. code-block:: bash

    $ php bin/console fos:user:deactivate testuser

Promote a User
--------------

The ``fos:user:promote`` command enables you to add a role to a user or make the
user a super administrator.

If you would like to add a role to a user you simply pass the ``username`` of the
user as the first argument to the command and the ``role`` to add to the user as
the second.

.. code-block:: bash

    $ php bin/console fos:user:promote testuser ROLE_ADMIN

You can promote a user to a super administrator by passing the ``--super`` option
after specifying the ``username``.

.. code-block:: bash

    $ php bin/console fos:user:promote testuser --super

If any of the arguments to the command are not specified then an interactive
prompt will ask you to enter them.

.. note::

    You may not specify the ``role`` argument and the ``--super`` option simultaneously.
    
.. caution::

    Changes will not be applied until the user logs out and back in again.

Demote a User
-------------

The ``fos:user:demote`` command is similar to the promote command except that
instead of adding a role to the user it removes it. You can also revoke a user's
super administrator status with this command.

If you would like to remove a role from a user you simply pass the ``username`` of
the user as the first argument to the command and the ``role`` to remove as the
second.

.. code-block:: bash

    $ php bin/console fos:user:demote testuser ROLE_ADMIN

To revoke the super administrator status of a user, simply pass the ``username`` as
an argument to the command as well as the ``--super`` option.

.. code-block:: bash

    $ php bin/console fos:user:demote testuser --super

If any of the arguments to the command are not specified then an interactive
prompt will ask you to enter them.

.. note::

    You may not specify the ``role`` argument and the ``--super`` option simultaneously.
    
.. caution::

    Changes will not be applied until the user logs out and back in again. This has 
    implications for the way in which you configure sessions in your application since
    you want to ensure that users are demoted as quickly as possible.

Change a User's Password
------------------------

The ``fos:user:change-password`` command provides an easy way to change a user's
password. The command takes two arguments, the ``username`` of the user whose
password you would like to change and the new ``password``.

.. code-block:: bash

    $ php bin/console fos:user:change-password testuser newp@ssword

If you do not specify the ``password`` argument then an interactive prompt will
ask you to enter one.
