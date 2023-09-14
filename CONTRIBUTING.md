# Contributing

## Workflow
Development is based off of the `main` branch. There is no `release` branch. Releases are cut from `main` as and when ready. Composer-based sites can pull the latest release as normal, whereas [pantheon-systems/WordPress](https://github.com/pantheon-systems/WordPress) -- which is used for the [WordPress Upstream](https://docs.pantheon.io/wordpress/) -- is updated through automation powered by [Update Tool](https://github.com/pantheon-systems/update-tool/).

Update Tool clones whatever the latest code on `main` is, and manually removes files that are not required for WordPress sites (e.g. `composer.json`, `composer.lock`, `README.md`, etc.). This is then bundled as part of WordPress releases and upstream updates.

Because the WordPress upstream is only updated when a new WordPress release is cut, it's less risky that we don't have an explicit `develop` branch, but `main` should still always be in a stable state in the chance that a WordPress bugfix or security release is pushed unexpectedly.

## Release Process

When you are ready for a new `pantheon-mu-plugin` release, before cutting a new release, there are a few steps that need to be taken before you do so.

1. Update the version number in `pantheon.php` in the plugin header and the `PANTHEON_MU_PLUGIN_VERSION` constant.
1. If there were any new files that were added to the plugin that should be excluded from the WordPress upstream, add them to the `.gitattributes` file with `export-ignore` and be sure to add them to the `$files_to_delete` array in [`update-tool/src/Update/Filters/CopyMuPlugin.php`](https://github.com/pantheon-systems/update-tool/blob/master/src/Update/Filters/CopyMuPlugin.php).
1. Use the GitHub UI to create a new release. The tag should be the version number only (not prefixed with `v`, e.g. `1.2.1`). Use the GitHub tools to autocomplete the title and body of the release with the changelog. The release should be created from the `main` branch.