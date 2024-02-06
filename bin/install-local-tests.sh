#!/bin/bash
set -e

# Request version.
echo "Which version of WordPress would you like to test against? (latest, nightly, or a version number)"
read -r WP_VERSION

# Initialize variables with default values
TMPDIR="/tmp"
DB_NAME="wordpress_test"
DB_USER="root"
DB_PASS=""
DB_HOST="127.0.0.1"
WP_VERSION=${WP_VERSION:-latest}
SKIP_DB=""

# Display usage information
usage() {
  echo "Usage:"
  echo "./install-local-tests.sh [--dbname=wordpress_test] [--dbuser=root] [--dbpass=''] [--dbhost=127.0.0.1] [--wpversion=latest] [--no-db]"
}

download() {
    if [ "$(which curl)" ]; then
        curl -s "$1" > "$2";
    elif [ "$(which wget)" ]; then
        wget -nv -O "$2" "$1"
    fi
}

# Parse command-line arguments
for i in "$@"
do
case $i in
    --dbname=*)
    DB_NAME="${i#*=}"
    shift
    ;;
    --dbuser=*)
    DB_USER="${i#*=}"
    shift
    ;;
    --dbpass=*)
    DB_PASS="${i#*=}"
    shift
    ;;
    --dbhost=*)
    DB_HOST="${i#*=}"
    shift
    ;;
    --wpversion=*)
    WP_VERSION="${i#*=}"
    shift
    ;;
    --no-db)
    SKIP_DB="true"
    shift
    ;;
    *)
    # unknown option
    usage
    exit 1
    ;;
esac
done

# Run install-wp-tests.sh
echo "Installing local tests into ${TMPDIR}"
echo "Using WordPress version: ${WP_VERSION}"
bash "$(dirname "$0")/install-wp-tests.sh" "$DB_NAME" "$DB_USER" "$DB_PASS" "$DB_HOST" "$WP_VERSION" "$SKIP_DB"

# If WP nightly is chosen, the script doesn't download the wp-latest.json file, so download it manually.
if [ "${WP_VERSION}" == "nightly" ]; then
  download http://api.wordpress.org/core/version-check/1.7/ /tmp/wp-latest.json

  # If a wp-config file does not exist, create it.
  if [ ! -f "${TMPDIR}/wordpress/wp-config.php" ]; then
    echo "Creating wp-config.php"
    wp config create --dbname="${DB_NAME}" --dbuser="${DB_USER}" --dbpass="${DB_PASS}" --dbhost="${DB_HOST}" --dbprefix=wptests_ --path="${TMPDIR}/wordpress/"
  fi
  
  wp core install --url=localhost --title=Test --admin_user=admin --admin_password=password --admin_email=test@dev.null --path="${TMPDIR}/wordpress/"
  # If nightly version of WP is installed, install latest Gutenberg plugin and activate it.
  echo "Installing Gutenberg plugin"
  wp plugin install gutenberg --activate --path="${TMPDIR}/wordpress/"
fi

# Run PHPUnit
echo "Running PHPUnit"
composer phpunit
