# testing/php

This directory contains all the files needed to run PHP unit tests. You should create a directory here for your own unit tests; see the `core` directory for an example of how to setup tests.

To run tests you first must create a test `config.php` file in this directory; see `testing/php/sample.config.php` for an example. Next run the following command from your terminal at the root directory of this project: `php smpan php:test`

You can create a setup file here (`testing/php/setup.php`) if you need to run some code before unit tests are performed.

**NOTE:** Many things are already setup for you. You can review (but not alter) the `bootstrap` file found in `testing/php/bin/bootstrap.php` for more information.
