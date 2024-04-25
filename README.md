# Pantheon Must-Use Plugin

[![Actively Maintained](https://img.shields.io/badge/Pantheon-Actively_Maintained-yellow?logo=pantheon&color=FFDC28)](https://docs.pantheon.io/oss-support-levels#actively-maintained-support)
[![Test](https://github.com/pantheon-systems/pantheon-mu-plugin/actions/workflows/test.yml/badge.svg)](https://github.com/pantheon-systems/pantheon-mu-plugin/actions/workflows/test.yml)
![GitHub Release](https://img.shields.io/github/v/release/pantheon-systems/pantheon-mu-plugin)
![GitHub License](https://img.shields.io/github/license/pantheon-systems/pantheon-mu-plugin)

The Pantheon Must-Use Plugin has been designed to tailor the WordPress CMS experience for Pantheon's platform.

What does that mean? We're glad you asked!

## Features

### WebOps Workflow
**Integrates WordPress with Pantheon Worklow.** Encourages updating plugins and themes in the Development environment and using Pantheon's git-based upstream core updates. Alerts admins if an update is available but disables automatic updates (so those updates can be applied via the upstream).

### Login
**Customized login form.** The login page links back to the Pantheon dashboard on dev, test and live environments that do not have a domain attached.

### Edge Cache (Global CDN)
**Facilitates communication between Pantheon's Edge Cache layer and WordPress.** It allows you to set the default cache age, clear individual pages on demand, and it will automatically clear relevant urls when the site is updated. Authored by [Matthew Boynes](http://www.alleyinteractive.com/).

### WordPress Multisite Support
**Simplified multisite configuration.** The `WP_ALLOW_MULTISITE` is automatically defined on WordPress Multisite-based upstreams. The Network Setup pages and notices have been customized for a Pantheon-specific WordPress multisite experience.

### Maintenance Mode
**Put your site into a maintenance mode.** Prevent users from accessing your sites during major updates by enabling Maintenance Mode either in the WordPress admin or via WP-CLI.

## Hooks

The Pantheon Must-Use Plugin provides the following hooks that can be used in your code:

### Filters

#### `pantheon_wp_login_text`
Filter the text displayed on the login page next to the Return to Pantheon button.

**Default Value:** `Log into your WordPress Site`

**Example:**
```php
add_filter( 'pantheon_wp_login_text', function() {
	return 'Log into MySite.';
} );
```

#### `pantheon_cache_default_max_age`
Filter the default cache max-age for the Pantheon Edge Cache.

**Default Value:** `WEEK_IN_SECONDS` (604800)

**Example:**
```php
add_filter( 'pantheon_cache_default_max_age', function() {
    return 2 * WEEK_IN_SECONDS;
} );
```

#### `pantheon_cache_do_maintenance_mode`
Allows you to modify the maintenance mode behavior with more advanced conditionals.

**Default Value:** Boolean, depending on whether maintenance mode is enabled, user is not on the login page and the action is not happening in WP-CLI.

```php
add_filter( 'pantheon_cache_do_maintenance_mode', function( $do_maintenance_mode ) {
	if ( $some_conditional_logic ) {
		return false;
	}
	return $do_maintenance_mode;
} );
```

#### `pantheon_cache_allow_clear_all`
Allows you to disable the ability to clear the entire cache from the WordPress admin. If set to `false`, this removes the "Clear Site Cache" section of the Pantheon Page Cache admin page.

**Default Value:** `true`

**Example:**
```php
add_filter( 'pantheon_cache_allow_clear_all', '__return_false' );
```

### Actions
#### `pantheon_cache_settings_page_top`
Runs at the top of the Pantheon Page Cache settings page.

**Example:**
```php
add_action( 'pantheon_cache_settings_page_top', function() {
	echo '<h2>My Custom Heading</h2>';
} );
```

#### `pantheon_cache_settings_page_bottom`
Runs at the bottom of the Pantheon Page Cache settings page.

**Example:**
```php
add_action( 'pantheon_cache_settings_page_bottom', function() {
	echo '<p>My Custom Footer</p>';
} );
```

## Install With Composer
**Built for Composer.** While Pantheon automation ensures that the latest version of the MU plugin are pushed with every update to WordPress, the Composer-based project ensures that you can manage it alongside your other WordPress mu-plugins, plugins and themes in your `composer.json`.

```bash
composer require pantheon-systems/pantheon-mu-plugin
```
--
Maintained by [Pantheon](https://pantheon.io) and built by the [community](https://github.com/pantheon-systems/pantheon-mu-plugin/graphs/contributors).

[Releases and Changelogs](https://github.com/pantheon-systems/pantheon-mu-plugin/releases)
