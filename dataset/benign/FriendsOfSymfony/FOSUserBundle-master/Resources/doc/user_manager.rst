About FOSUserBundle User Manager
================================

In order to be storage agnostic, all operations on the user instances are
handled by a user manager implementing ``FOS\UserBundle\Model\UserManagerInterface``.
Using it ensures that your code will continue to work if you change the storage.
The controllers provided by the bundle use the configured user manager instead
of interacting directly with the storage layer.

If you configure the ``db_driver`` option to ``orm``, this service is an instance
of ``FOS\UserBundle\Doctrine\UserManager``.

If you configure the ``db_driver`` option to ``mongodb``, this service is an
instance of ``FOS\UserBundle\Doctrine\UserManager``.

If you configure the ``db_driver`` option to ``couchdb``, this service is an
instance of ``FOS\UserBundle\Doctrine\UserManager``.

Accessing the User Manager service
----------------------------------

The user manager is available in the container as the ``fos_user.user_manager``
service.

.. code-block:: php

    $userManager = $container->get('fos_user.user_manager');

Creating a new User
-------------------

A new instance of your User class can be created by the user manager.

.. code-block:: php

    $user = $userManager->createUser();

``$user`` is now an instance of your user class.

.. note::

    This method will not work if your user class has some mandatory constructor
    arguments.

Retrieving the users
--------------------

The user manager has a few methods to find users based on the unique fields
(username, email and confirmation token) and a method to retrieve all existing
users.

- ``findUserByUsername($username)``
- ``findUserByEmail($email)``
- ``findUserByUsernameOrEmail($value)``  (check if the value looks like an email to choose)
- ``findUserByConfirmationToken($token)``
- ``findUserBy(array('id'=>$id))``
- ``findUsers()``

To save a user object, you can use the ``updateUser`` method of the user manager.
This method will update the encoded password and the canonical fields and
then persist the changes.

Updating a User object
----------------------

.. code-block:: php

    $user = $userManager->createUser();
    $user->setUsername('John');
    $user->setEmail('john.doe@example.com');

    $userManager->updateUser($user);

.. note::

    To make it easier, the bundle comes with a Doctrine listener handling
    the update of the password and the canonical fields for you behind the
    scenes. If you always save the user through the user manager, you may
    want to disable it to improve performance.

    .. configuration-block::

        .. code-block:: yaml

            # app/config/config.yml
            fos_user:
                # ...
                use_listener: false

        .. code-block:: xml

            <!-- app/config/config.xml -->
            <fos_user:config
                db-driver="orm"
                firewall-name="main"
                use-listener="false"
                user-class="MyProject\MyBundle\Entity\User"
            />

.. note::

    For the Doctrine implementations, the default behavior is to flush the
    unit of work when calling the ``updateUser`` method. You can disable the
    flush by passing a second argument set to ``false``.
    This will then be equivalent to calling ``updateCanonicalFields`` and
    ``updatePassword``.

An ORM example::

    class MainController extends Controller
    {
        public function updateAction($id)
        {
            $user = // get a user from the datastore

            $user->setEmail($newEmail);

            $this->get('fos_user.user_manager')->updateUser($user, false);

            // make more modifications to the database

            $this->getDoctrine()->getManager()->flush();
        }
    }

Overriding the User Manager
---------------------------

You can replace the default implementation of the user manager by defining
a service implementing ``FOS\UserBundle\Model\UserManagerInterface`` and
setting its id in the configuration.
The id of the default implementation is ``fos_user.user_manager.default``

.. code-block:: yaml

    fos_user:
        # ...
        service:
            user_manager: custom_user_manager_id

Your custom implementation can extend ``FOS\UserBundle\Model\UserManager``
to reuse the common logic.

SecurityBundle integration
--------------------------

The bundle provides several implementation of ``Symfony\Component\Security\Core\UserProviderInterface``
on top of the ``UserManagerInterface``.

Although the built-in user managers also implement
``Symfony\Component\Security\Core\User\UserProviderInterface``, using the
UserManager as user provider is deprecated and will tbe removed in future
versions. Use ``FOS\UserBundle\Security\UserProvider`` instead.
