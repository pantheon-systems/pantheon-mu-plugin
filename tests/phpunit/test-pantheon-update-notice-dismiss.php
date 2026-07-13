<?php
/**
 * Pantheon update-notice dismiss AJAX handler tests.
 *
 * @package pantheon
 */

/**
 * Tests for the wp_ajax_pantheon_dismiss_update_notice handler.
 *
 * @group ajax
 */
class Test_Pantheon_Update_Notice_Dismiss extends WP_Ajax_UnitTestCase {

	/**
	 * Simulate that a core update (99.0.0) is available.
	 */
	private function simulate_core_update_available() {
		set_site_transient(
			'update_core',
			(object) [
				'updates' => [
					(object) [
						'current'  => '99.0.0',
						'response' => 'upgrade',
						'locale'   => 'en_us',
					],
				],
				'version_checked' => get_bloginfo( 'version' ),
			]
		);
	}

	/**
	 * A valid dismiss request stores the current available version in user meta.
	 */
	public function test_dismiss_stores_available_version() {
		$this->simulate_core_update_available();
		$user_id = self::factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user_id );

		$_POST['nonce'] = wp_create_nonce( PANTHEON_UPDATE_NOTICE_DISMISS_ACTION );

		try {
			$this->_handleAjax( PANTHEON_UPDATE_NOTICE_DISMISS_ACTION );
		} catch ( WPAjaxDieContinueException $e ) {
			// wp_send_json_success() dies; expected in the success path.
			unset( $e );
		}

		$response = json_decode( $this->_last_response, true );
		$this->assertTrue( $response['success'] );
		$this->assertEquals( '99.0.0', get_user_meta( $user_id, PANTHEON_UPDATE_NOTICE_DISMISSED_META, true ) );
	}

	/**
	 * A request with an invalid nonce is rejected and stores nothing.
	 */
	public function test_dismiss_rejects_bad_nonce() {
		$this->simulate_core_update_available();
		$user_id = self::factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user_id );

		$_POST['nonce'] = 'invalid-nonce';

		try {
			$this->expectException( WPAjaxDieStopException::class );
			$this->_handleAjax( PANTHEON_UPDATE_NOTICE_DISMISS_ACTION );
		} finally {
			$this->assertEmpty( get_user_meta( $user_id, PANTHEON_UPDATE_NOTICE_DISMISSED_META, true ) );
		}
	}
}
