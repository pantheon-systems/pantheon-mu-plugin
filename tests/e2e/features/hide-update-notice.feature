Feature: Hide the Pantheon WordPress update notice
  As a site admin who already tracks updates in the Pantheon dashboard
  I want to hide or disable the WordPress update notice
  So that editors are not shown the update nag

  Background:
    Given I am logged in to WordPress admin

  Scenario: The update notice exposes a unique id and class for targeting
    When I open the WordPress admin page "/wp-admin/update-core.php"
    Then the element "#pantheon-update-notice" should be visible
    And the element ".pantheon-notice.pantheon-update-notice" should be visible

  Scenario: CSS targeting the unique id hides the notice
    When I open the WordPress admin page "/wp-admin/update-core.php"
    Then the element "#pantheon-update-notice" should be visible
    When I apply the CSS "#pantheon-update-notice { display: none; }"
    Then the element "#pantheon-update-notice" should be hidden
    And the element "#wpadminbar" should be visible

  Scenario: The pantheon_show_update_notice filter hides the notice
    When I open the WordPress admin page "/wp-admin/update-core.php"
    Then the element "#pantheon-update-notice" should be visible
    When the pantheon_show_update_notice filter returns false
    And I open the WordPress admin page "/wp-admin/update-core.php"
    Then the element "#pantheon-update-notice" should be hidden

  Scenario: The PANTHEON_SHOW_UPDATE_NOTICE constant hides the notice
    When I open the WordPress admin page "/wp-admin/update-core.php"
    Then the element "#pantheon-update-notice" should be visible
    When the PANTHEON_SHOW_UPDATE_NOTICE constant is set to false
    And I open the WordPress admin page "/wp-admin/update-core.php"
    Then the element "#pantheon-update-notice" should be hidden

  Scenario: The update notice renders as dismissible
    Given a WordPress core update is available
    When I open the WordPress admin page "/wp-admin/index.php"
    Then the update notice should offer a dismiss option

  Scenario: Dismissing the update notice persists across page loads
    Given a WordPress core update is available
    When I open the WordPress admin page "/wp-admin/index.php"
    Then the element "#pantheon-update-notice" should be visible
    When I dismiss the update notice
    And I open the WordPress admin page "/wp-admin/index.php"
    Then the element "#pantheon-update-notice" should be hidden

  Scenario: The dismissed notice returns when a newer version is available
    Given a WordPress core update is available
    And the update notice has been dismissed for the current version
    When a newer WordPress core update is released
    And I open the WordPress admin page "/wp-admin/index.php"
    Then the element "#pantheon-update-notice" should be visible
