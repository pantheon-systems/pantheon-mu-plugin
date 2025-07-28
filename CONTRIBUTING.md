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
    *   Set a property like `$run_fix_everytime = true;` to control when the fix runs.

2.  **Implement the Fix Logic (Choose One):**

    *   **Option A (Simple Fix, No Fix Class):** Place your logic directly inside the `apply_fix` method of your new Compatibility Class.

        *Example:*
        ```php
        // in inc/compatibility/class-simplefixplugin.php
        public function apply_fix() {
            if ( ! defined( 'SIMPLE_FIX' ) ) {
                define( 'SIMPLE_FIX', true );
            }
        }
        ```

    *   **Option B (Recommended, Using a Fix Class):**
        *   Create a new file in `inc/compatibility/fixes/` named `class-{plugin-name}fix.php`.
        *   Create a static `apply()` method in this class containing your fix logic.
        *   Call this static method from your Compatibility Class's `apply_fix()` method.

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

3.  **Register the Compatibility Class:**
    *   In `inc/compatibility/class-compatibilityfactory.php`, add your new **Compatibility Class** and the target plugin's slug to the `$targets` array in the `setup_targets()` method.

4.  **Add a Test:**
    *   In `tests/phpunit/test-compatibility-layer.php`, add a test to ensure your component is instantiated correctly.