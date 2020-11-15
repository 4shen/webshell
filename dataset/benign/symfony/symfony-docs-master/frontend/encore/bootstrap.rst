Using Bootstrap CSS & JS
========================

Want to use Bootstrap (or something similar) in your project? No problem!
First, install it. To be able to customize things further, we'll install
``bootstrap``:

.. code-block:: terminal

    $ yarn add bootstrap --dev

Importing Bootstrap Styles
--------------------------

Now that ``bootstrap`` lives in your ``node_modules/`` directory, you can
import it from any Sass or JavaScript file. For example, if you already have
a ``global.scss`` file, import it from there:

.. code-block:: scss

    // assets/css/global.scss

    // customize some Bootstrap variables
    $primary: darken(#428bca, 20%);

    // the ~ allows you to reference things in node_modules
    @import "~bootstrap/scss/bootstrap";

That's it! This imports the ``node_modules/bootstrap/scss/bootstrap.scss``
file into ``global.scss``. You can even customize the Bootstrap variables first!

.. tip::

    If you don't need *all* of Bootstrap's features, you can include specific files
    in the ``bootstrap`` directory instead - e.g. ``~bootstrap/scss/alert``.

Importing Bootstrap JavaScript
------------------------------

Bootstrap JavaScript requires jQuery and Popper.js, so make sure you have this installed:

.. code-block:: terminal

    $ yarn add jquery popper.js --dev

Now, require bootstrap from any of your JavaScript files:

.. code-block:: javascript

    // app.js

    const $ = require('jquery');
    // this "modifies" the jquery module: adding behavior to it
    // the bootstrap module doesn't export/return anything
    require('bootstrap');

    // or you can include specific pieces
    // require('bootstrap/js/dist/tooltip');
    // require('bootstrap/js/dist/popover');

    $(document).ready(function() {
        $('[data-toggle="popover"]').popover();
    });

Using other Bootstrap / jQuery Plugins
--------------------------------------

If you need to use jQuery plugins that work well with jQuery, you may need to use
Encore's :ref:`autoProvidejQuery() <encore-autoprovide-jquery>` method so that
these plugins know where to find jQuery. Then, you can include the needed JavaScript
and CSS like normal:

.. code-block:: javascript

    // ...

    // require the JavaScript
    require('bootstrap-star-rating');
    // require 2 CSS files needed
    require('bootstrap-star-rating/css/star-rating.css');
    require('bootstrap-star-rating/themes/krajee-svg/theme.css');
