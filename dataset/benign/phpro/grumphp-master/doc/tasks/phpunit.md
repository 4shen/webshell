# Phpunit

The Phpunit task will run your unit tests.

***Composer***

```
composer require --dev phpunit/phpunit
```

***Config***

The task lives under the `phpunit` namespace and has following configurable parameters:

```yaml
# grumphp.yml
grumphp:
    tasks:
        phpunit:
            config_file: ~
            testsuite: ~
            group: []
            always_execute: false
```

**config_file**

*Default: null*

If your phpunit.xml file is located at an exotic location, you can specify your custom config file location with this option.
This option is set to `null` by default.
This means that `phpunit.xml` or `phpunit.xml.dist` are automatically loaded if one of them exist in the current directory.


**testsuite**

*Default: null*

If you wish to only run tests from a certain Suite.
`testsuite: unit`


**group**

*Default: array()*

If you wish to only run tests from a certain Group.
`group: [fast,quick,small]`


**always_execute**

*Default: false*

Always run the whole test suite, even if no PHP files were changed.

