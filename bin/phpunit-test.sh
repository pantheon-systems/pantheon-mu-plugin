#!/bin/bash

set -ex

DIRNAME=$(dirname "$0")

download() {
    if [ "$(which curl)" ]; then
        curl -s "$1" > "$2";
    elif [ "$(which wget)" ]; then
        wget -nv -O "$2" "$1"
    fi
}

echo "🤔 Installing WP Unit tests..."
bash "${DIRNAME}/install-wp-tests.sh" wordpress_test root root 127.0.0.1 latest

echo "📄 Copying wp-latest.json..."
cp /tmp/wp-latest.json "${DIRNAME}/../tests/wp-latest.json"

echo "🏃‍♂️ Running PHPUnit on Single Site"
composer phpunit --ansi

echo "🧹 Removing files before testing WPMS..."
rm "${DIRNAME}/../tests/wp-latest.json"

bash "${DIRNAME}/install-wp-tests.sh" wordpress_test root root 127.0.0.1 latest true
echo "🏃‍♂️ Running PHPUnit on Multisite"
composer test:multisite --ansi

setup_wp_nightly() {
	download http://api.wordpress.org/core/version-check/1.7/ /tmp/wp-latest.json
	echo "Creating wp-config.php"
	wp config create --dbname=wordpress_test --dbuser=root --dbpass=root --dbhost=127.0.0.1 --dbprefix=wptests_ --path="/tmp/wordpress"
	wp core install --url=localhost --title=Test --admin_user=admin --admin_password=password --admin_email=test@dev.null --path="/tmp/wordpress"
	# If nightly version of WP is installed, install latest Gutenberg plugin and activate it.
	echo "Installing Gutenberg plugin"
	wp plugin install gutenberg --activate --path="/tmp/wordpress"
}

echo "🧹 Removing files before testing nightly WP..."

echo "🤔 Installing WP Unit tests with WP nightly version..."
bash "${DIRNAME}/install-wp-tests.sh" wordpress_test root root 127.0.0.1 nightly true
echo "📄 Copying wp-latest.json..."
cp /tmp/wp-latest.json "${DIRNAME}/../tests/wp-latest.json"

setup_wp_nightly

echo "🏃‍♂️ Running PHPUnit on Single Site (Nightly WordPress)"
composer phpunit --ansi
