# UndefinedMagicPropertyFetch

Emitted when getting a property on an object that doesn’t have that magic property defined

```php
<?php

/**
 * @property string $bar
 */
class A {
    public function __get(string $name) {
        return "cool";
    }
}
$a = new A();
echo $a->foo;
```
