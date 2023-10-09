#!/bin/bash

set -e

DIRNAME=$(dirname "$0")

echo "🤔 Installing WP Unit tests..."
bash "${DIRNAME}/install-wp-tests.sh" wordpress_test root root 127.0.0.1 latest

echo "🏃‍♂️ Running PHPUnit on Single Site"
composer phpunit --ansi

echo "🧹 Removing files before testing nightly WP..."
rm -rf "$WP_TESTS_DIR" "$WP_CORE_DIR"

echo "🤔 Installing WP Unit tests with WP nightly version..."
bash "${DIRNAME}/install-wp-tests.sh" wordpress_test root root 127.0.0.1 nightly true
echo "🏃‍♂️ Running PHPUnit on Single Site (Nightly WordPress)"
composer phpunit --ansi

bash "${DIRNAME}/install-wp-tests.sh" wordpress_test root root 127.0.0.1 latest true
echo "🏃‍♂️ Running PHPUnit on Multisite"
composer test:multisite --ansi
