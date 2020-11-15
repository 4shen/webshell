Compound
========

To the contrary to the other constraints, this constraint cannot be used on its own.
Instead, it allows you to create your own set of reusable constraints, representing
rules to use consistently across your application, by extending the constraint.

.. versionadded:: 5.1

    The ``Compound`` constraint was introduced in Symfony 5.1.

==========  ===================================================================
Applies to  :ref:`class <validation-class-target>` or :ref:`property or method <validation-property-target>`
Options     - `groups`_
            - `payload`_
Class       :class:`Symfony\\Component\\Validator\\Constraints\\Compound`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\CompoundValidator`
==========  ===================================================================

Basic Usage
-----------

Suppose that you have different places where a user password must be validated,
you can create your own named set or requirements to be reused consistently everywhere::

    // src/Validator/Constraints/PasswordRequirements.php
    namespace App\Validator\Constraints;

    use Symfony\Component\Validator\Compound;
    use Symfony\Component\Validator\Constraints as Assert;

    /**
     * @Annotation
     */
    class PasswordRequirements extends Compound
    {
        protected function getConstraints(array $options): array
        {
            return [
                new Assert\NotBlank(),
                new Assert\Type('string'),
                new Assert\Length(['min' => 12]),
                new Assert\NotCompromisedPassword(),
            ];
        }
    }

You can now use it anywhere you need it:

.. configuration-block::

    .. code-block:: php-annotations

        // src/User/RegisterUser.php
        namespace App\User;

        use App\Validator\Constraints as AcmeAssert;

        class RegisterUser
        {
            /**
             * @AcmeAssert\PasswordRequirements()
             */
            public $password;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\User\RegisterUser:
            properties:
                password:
                    - App\Validator\Constraints\PasswordRequirements: ~

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\User\RegisterUser">
                <property name="password">
                    <constraint name="App\Validator\Constraints\PasswordRequirements"/>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/User/RegisterUser.php
        namespace App\User;

        use App\Validator\Constraints as AcmeAssert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class RegisterUser
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('password', new AcmeAssert\PasswordRequirements());
            }
        }

Options
-------

.. include:: /reference/constraints/_groups-option.rst.inc

.. include:: /reference/constraints/_payload-option.rst.inc
