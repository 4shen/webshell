#!/bin/sh
#
# This file is part of the Zephir.
#
# (c) Phalcon Team <team@zephir-lang.com>
#
# For the full copyright and license information, please view
# the LICENSE file that was distributed with this source code.

# -e  Exit immediately if a command exits with a non-zero status.
# -u  Treat unset variables as an error when substituting.
set -eu

if [ "$(php-config --vernum)" -lt "70200" ]; then
  test_suite="Extension_Php70"
else
  test_suite="Extension_Php72"
fi

vendor/bin/simple-phpunit --version

php \
  -d extension=ext/modules/stub.so \
  vendor/bin/simple-phpunit \
  --colors=always \
  --bootstrap tests/ext-bootstrap.php \
  --testsuite ${test_suite}

php \
  vendor/bin/simple-phpunit \
  --colors=always \
  --testsuite Zephir
