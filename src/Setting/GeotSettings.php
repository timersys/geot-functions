<?php
namespace GeotFunctions\Setting;


class GeotSettings {

	public static function init() {
		add_action( 'admin_menu' , [ self::class, 'add_settings_menu' ]);
		add_action( 'admin_init' , [ self::class, 'save_settings' ]);
	}
	/**
	 * Add menu for Settings page of the plugin
	 * @since  1.0.0
	 * @return  void
	 */
	public function add_settings_menu() {

		add_menu_page('GeoTargetingWP', 'GeoTargetingWP', 'manage_options', 'geot-settings', array($this, 'settings_page'), 'dashicons-admin-site' );
		add_submenu_page( 'geot-settings', 'Settings', 'Settings', 'manage_options', 'geot-settings',array($this, 'settings_page') );
		add_submenu_page( 'geot-settings', 'Debug data', 'Debug data', 'manage_options', 'geot-debug-data',array($this, 'debug_data_page') );
	}

	/**
	 * Settings page for plugin
	 * @since 1.0.0
	 */
	public function settings_page() {
			include  dirname( __FILE__ )  . '/partials/settings-page.php';
	}

	/**
	 * Save the settings page
	 * @since 1.0.0
	 * @return void
	 */
	public function save_settings(){
		if (  isset( $_POST['geot_nonce'] ) && wp_verify_nonce( $_POST['geot_nonce'], 'geot_save_settings' ) ) {
			$settings = esc_sql( $_POST['geot_settings'] );
			if( isset($_FILES['geot_settings_json']) && 'application/json' == $_FILES['geot_settings_json']['type'] ) {
				$file = file_get_contents($_FILES['geot_settings_json']['tmp_name']);
				$settings = json_decode($file,true);

			}
			update_option( 'geot_settings' ,  $settings);
		}
	}

	/**
	 * Debug Data page
	 */
	public function debug_data_page() {
		include dirname( __FILE__ )  . '/partials/ip-test.php';
	}
}

GeotSettings::init();
error_log('called')