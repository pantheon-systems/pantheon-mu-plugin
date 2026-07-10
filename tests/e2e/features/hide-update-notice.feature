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
    And I apply the CSS "#pantheon-update-notice { display: none; }"
    Then the element "#pantheon-update-notice" should be hidden
    And the element "#wpadminbar" should be visible

  Scenario: The pantheon_show_update_notice filter hides the notice
    When the pantheon_show_update_notice filter returns false
    And I open the WordPress admin page "/wp-admin/update-core.php"
    Then the element "#pantheon-update-notice" should be hidden

  Scenario: The PANTHEON_HIDE_UPDATE_NOTICE constant hides the notice
    When the PANTHEON_HIDE_UPDATE_NOTICE constant is set to true
    And I open the WordPress admin page "/wp-admin/update-core.php"
    Then the element "#pantheon-update-notice" should be hidden
