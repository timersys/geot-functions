<?php
namespace GeotFunctions\Setting;


class GeotSettings {

	public function __construct() {
		add_action( 'admin_menu' , [ $this, 'add_settings_menu' ],8);
		add_action( 'admin_init' , [ $this, 'save_settings' ]);
		add_action( 'admin_init' , [ $this, 'check_license' ],15);
	}

	/**
	 * Init the class
	 * @return GeotSettings
	 */
	public static function init(){
		return new self();
	}

	/**
	 * Check for license or show notice
	 */
	public function check_license(){
		$opts = geot_settings();
		if( empty($opts['license']) || empty($opts['api_secret']))
			add_action( 'admin_notices' , [ $this, 'license_missing_notice'], 10 );
	}

	/**
	 * License missing message
	 */
	public function license_missing_notice(){
		?><div class="notice notice-error">
		<h3>GeotargetingPro</h3>
		<p><?php __( printf( 'In order to to use the plugin you need to enter the api keys in the <a href="%1$s">settings page</a>.', admin_url('admin.php?page=geot-settings'), 'geot' ) );?></p>
		</div>
		<?php
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
		include dirname( __FILE__ )  . '/partials/debug-data.php';
	}
}
