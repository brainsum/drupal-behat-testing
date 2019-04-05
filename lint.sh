#!/usr/bin/env bash

echo "PHP Lint.."
vendor/bin/parallel-lint src

echo "PHPCS.."
vendor/bin/phpcs src
