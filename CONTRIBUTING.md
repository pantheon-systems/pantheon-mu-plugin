# Contributing

## Workflow
Development is based off of the `main` branch. There is no `release` branch. Releases are cut from `main` as and when ready. Composer-based sites can pull the latest release as normal, whereas [pantheon-systems/WordPress](https://github.com/pantheon-systems/WordPress) -- which is used for the [WordPress Upstream](https://docs.pantheon.io/wordpress/) -- is updated through automation powered by [Update Tool](https://github.com/pantheon-systems/update-tool/).

Update Tool clones whatever the latest code on `main` is, and manually removes files that are not required for WordPress sites (e.g. `composer.json`, `composer.lock`, `README.md`, etc.). This is then bundled as part of WordPress releases and upstream updates.

Because the WordPress upstream is only updated when a new WordPress release is cut, it's less risky that we don't have an explicit `develop` branch, but `main` should still always be in a stable state in the chance that a WordPress bugfix or security release is pushed unexpectedly.

## Release Process

Releases are automated via GitHub Actions and gated by the version string in `pantheon.php`.

### How it works

The version appears in two places in `pantheon.php` that must always match:

1. The plugin header comment: `* Version: 1.5.7-dev`
2. The PHP constant: `define( 'PANTHEON_MU_PLUGIN_VERSION', '1.5.7-dev' );`

When a commit lands on `main`, the release workflow reads the version and applies the following logic:

- **Ends in `-dev`** (e.g. `1.5.7-dev`): do nothing. This is the normal working state.
- **Does not end in `-dev`** (e.g. `1.5.7`): the workflow automatically:
  1. Creates a Git tag for that version (no `v` prefix, per existing convention).
  2. Publishes a GitHub Release with auto-generated release notes.
  3. Increments the patch version and appends `-dev` (e.g. `1.5.7` → `1.5.8-dev`), updating both occurrences in `pantheon.php`.
  4. Opens a PR with the bump and enables auto-merge — it merges automatically.

### Shipping a release

Update both version strings in `pantheon.php` on your PR before merging:

| Goal | Change in `pantheon.php` |
|---|---|
| No release (normal PR) | Leave as `X.Y.Z-dev` |
| Patch release | `1.5.7-dev` → `1.5.7` |
| Minor release | `1.5.7-dev` → `1.6.0` |
| Major release | `1.5.7-dev` → `2.0.0` |

After a release, automation sets the working version to the next patch `-dev`. If the next release should be a minor or major bump, update `pantheon.php` again on a subsequent PR.

### Manual steps that remain

If new files were added that should be excluded from the WordPress upstream, add them to `.gitattributes` with `export-ignore`.

## Contributing to the Compatibility Layer

There are two main ways to contribute to the compatibility layer: reporting an issue with a plugin or adding an automated fix.

### Type 1: Reporting an Incompatibility (No Code Fix)

Use this method when a plugin is incompatible and requires manual user action, or when there is no programmatic fix. These notices appear in the WordPress Site Health tool.

1. **Choose the correct category in `inc/site-health.php`:**
    * **`get_compatibility_manual_fixes()`**: For plugins that require specific manual configuration. The user will be told a "Manual Fix Required".
    * **`get_compatibility_review_fixes()`**: For plugins that are partially or fully incompatible. The user will see statuses like "Incompatible" or "Partial Compatibility".

2. **Add the plugin to the appropriate function's `$plugins` array.**

    *Example (Adding a "Manual Fix Required" notice):*
    ```php
    // In get_compatibility_manual_fixes() in inc/site-health.php
    $plugins = [
        // ... existing plugins
        'my-other-plugin' => [
            'plugin_status' => esc_html__( 'Manual Fix Required', 'pantheon' ),
            'plugin_slug' => 'my-other-plugin/my-other-plugin.php',
            'plugin_message' => wp_kses_post( 'This plugin requires manual configuration. See <a href="...">docs</a>.' ),
        ],
    ];
    ```

### Type 2: Adding an Automated Fix (Code Fix)

Use this method when you can fix an incompatibility with code. This involves creating a **Compatibility Class** and, optionally, a **Fix Class**.

#### Understanding the Classes

* **Compatibility Class (The "When"):** This class is the trigger. It tells the system *when* to run a fix for a specific plugin. It extends `Pantheon\Compatibility\Base` and is stored in `inc/compatibility/`. Its primary job is to define the conditions for the fix (e.g., run on every page load, only on activation).

* **Fix Class (The "What"):** This class contains the *actual code* that solves the problem (e.g., defines a constant, adds a filter). It is stored in `inc/compatibility/fixes/`. **Using a separate Fix Class is optional but highly recommended for clarity and reusability.** For very simple, one-line fixes, you can place the logic directly in the Compatibility Class. For anything more complex, or for logic that could be reused (like `DefineConstantFix`), a Fix Class is the best practice.

#### How to Implement an Automated Fix

1. **Create the Compatibility Class (Required):**
    * Create a new file in `inc/compatibility/` named `class-{plugin-name}.php`.
    * The class must extend `Pantheon\Compatibility\Base`.
    * You must implement the `apply_fix()` and `remove_fix()` methods, even if their bodies are empty.
    * Set a property like `$run_fix_everytime = true;` to control when the fix runs.

2. **Implement the Fix Logic (Choose One):**

    * **Option A (Simple Fix, No Fix Class):** Place your logic directly inside the `apply_fix` method of your new Compatibility Class.

        *Example:*
        ```php
        // in inc/compatibility/class-simplefixplugin.php
        public function apply_fix() {
            if ( ! defined( 'SIMPLE_FIX' ) ) {
                define( 'SIMPLE_FIX', true );
            }
        }
        ```

    * **Option B (Recommended, Using a Fix Class):**
        * Create a new file in `inc/compatibility/fixes/` named `class-{fix-name}fix.php` unless the fix is generic enough that an existing fix would work for your use case.
            * Fixes already exist for things like defining a constant, adding a filter or deleting a file.
            * If a fix for a specific plugin is required, name your fix class `class-{plugin-name}fix.php`.
        * If creating a new fix...
            * Create a static `apply()` method in this class containing your fix logic.
            * Call this static method from your Compatibility Class's `apply_fix()` method.

        *Example:*
        ```php
        // in inc/compatibility/fixes/class-complexfix.php
        class ComplexFix {
            public static function apply() {
                // ... complex logic here ...
            }
        }

        // in inc/compatibility/class-complexplugin.php
        public function apply_fix() {
            \Pantheon\Compatibility\Fixes\ComplexFix::apply();
        }
        ```

3. **Register the Compatibility Class:**
    * In `inc/compatibility/class-compatibilityfactory.php`, add your new **Compatibility Class** and the target plugin's slug to the `$targets` array in the `setup_targets()` method.

4. **Add a Test:**
    * In `tests/phpunit/test-compatibility-layer.php`, add a test to ensure your component is instantiated correctly.