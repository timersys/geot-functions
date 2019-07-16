<?php

namespace GeotFunctions\Notice;

class GeotNotices {


	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.3.1
	 */
	public function __construct() {

		if ( isset( $_GET['geot_notice'] ) ) {
			update_option( 'geot_' . esc_attr( $_GET['geot_notice'] ), true );
		}

		if ( getenv( 'HTTP_GEOIP_COUNTRY_CODE' ) !== false && ! get_option( 'geot_wpengine_dismiss' ) ) {
			add_action( 'admin_notices', [ self::class, 'wpengine' ] );
		}

		if ( ! empty( $_SERVER['HTTP_GEOIP_CITY_COUNTRY_NAME'] ) && ! get_option( 'geot_kinsta_dismiss' ) ) {
			add_action( 'admin_notices', [ self::class, 'kinsta' ] );
		}
		add_action( 'admin_notices', [ self::class, 'updategeowp' ] );
	}

	public static function wpengine() {
		?>
		<div class="notice-info error">
		<h3><i class=" dashicons-before dashicons-admin-site"></i> GeotargetingWP WPEngine</h3>
		<p>We detected that your have WPEngine Geolocation enabled on your hosting.</p>
		<p>Please go to the <a href="<?php echo admin_url( 'admin.php?page=geot-settings' ); ?>">settings page</a> and
			enable it for using it with the GeotargetingWP plugins.</p>
		<p><a href="<?= admin_url( '?geot_notice=wpengine_dismiss' ); ?>"
		      class="button-primary"><?php _e( 'Dismiss', 'geot' ); ?></a></p>
		</div><?php
	}

	public static function kinsta() {
		?>
		<div class="notice-info error">
		<h3><i class=" dashicons-before dashicons-admin-site"></i> GeotargetingWP Kinsta</h3>
		<p>We detected that your have Kinsta Geolocation enabled on your hosting.</p>
		<p>Please go to the <a href="<?php echo admin_url( 'admin.php?page=geot-settings' ); ?>">settings page</a> and
			enable it for using it with the GeotargetingWP plugins.</p>
		<p><a href="<?= admin_url( '?geot_notice=kinsta_dismiss' ); ?>"
		      class="button-primary"><?php _e( 'Dismiss', 'geot' ); ?></a></p>
		</div><?php
	}


	public static function updategeowp() {
		?>
		<div class="notice-info error">
		<h3><i class=" dashicons-before dashicons-admin-site"></i> GeotargetingWP</h3>
		<p>We launched a new version of GeotargetingWP "all in one" that will replace all existing plugins among some other new features.</p>
		<p>Please go to your <a href="https://geotargetingwp.com/dashboard">account page</a> and
			download the new version manually. Once installed and activated you can safely delete old plugins.</p>
		</div><?php
	}
}
