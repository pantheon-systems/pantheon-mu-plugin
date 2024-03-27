# Pantheon Must-Use Plugin

[![Actively Maintained](https://img.shields.io/badge/Pantheon-Actively_Maintained-yellow?logo=pantheon&color=FFDC28)](https://docs.pantheon.io/oss-support-levels#actively-maintained-support)
[![Test](https://github.com/pantheon-systems/pantheon-mu-plugin/actions/workflows/test.yml/badge.svg)](https://github.com/pantheon-systems/pantheon-mu-plugin/actions/workflows/test.yml)
![GitHub Release](https://img.shields.io/github/v/release/pantheon-systems/pantheon-mu-plugin)
![GitHub License](https://img.shields.io/github/license/pantheon-systems/pantheon-mu-plugin)

The Pantheon Must-Use Plugin has been designed to tailor the WordPress CMS experience for Pantheon's platform.

What does that mean? We're glad you asked!

## Workflow
**Integrates WordPress with Pantheon Flow.** Encourages updating plugins and themes in the Development environment and using Pantheon's git-based upstream core updates. Alerts admins if an update is available but disables automatic updates (so those updates can be applied via the upstream).

## Login
**Customized login form.** The login page links back to the Pantheon dashboard on dev, test and live environments that do not have a domain attached.

## Edge Cache
**Facilitates communication between Pantheon's Edge Cache layer and WordPress.** It allows you to set the default cache age, clear individual pages on demand, and it will automatically clear relevant urls when the site is updated. Authored by [Matthew Boynes](http://www.alleyinteractive.com/).

## WordPress Multisite Support
**Simplifies multisite configuration.** The `WP_ALLOW_MULTISITE` is automatically defined on WordPress Multisite-based upstreams. The Network Setup pages and notices have been tailored to a Pantheon-specific WordPress multisite experience.

## Maintenance Mode
**Allows the site to be put into a maintenance mode.** Prevent users from accessing your sites during major updates by enabling Maintenance Mode either in the WordPress admin or via WP-CLI.

## Installation via Composer
**The Pantheon MU Plugin has been built to support Composer-based installs.** While Pantheon automation ensures that the latest version of the MU plugin are pushed with every update to WordPress, the Composer-based project ensures that you can manage it alongside your other WordPress mu-plugins, plugins and themes in your `composer.json`.

```bash
composer require pantheon-systems/pantheon-mu-plugin
```
--
Maintained by [Pantheon](https://pantheon.io) and built by the [community](https://github.com/pantheon-systems/pantheon-mu-plugin/graphs/contributors).

[Releases and Changelogs](https://github.com/pantheon-systems/pantheon-mu-plugin/releases)
