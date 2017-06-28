<?php
namespace GeotFunctions\Setting;


class GeotSettings {

	/**
	 * Plugin Instance
	 * @since 1.0.0
	 * @var The Fbl plugin instance
	 */
	protected static $_instance = null;

	/**
	 * Main plugin_name Instance
	 *
	 * Ensures only one instance of WSI is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see Geotr()
	 * @return plugin_name - Main instance
	 */
	public static function init() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 * @since 1.0.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'wsi' ), '2.1' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 * @since 1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'wsi' ), '2.1' );
	}

	/**
	 * Auto-load in-accessible properties on demand.
	 * @param mixed $key
	 * @since 1.0.0
	 * @return mixed
	 */
	public function __get( $key ) {
		if ( in_array( $key, array( 'payment_gateways', 'shipping', 'mailer', 'checkout' ) ) ) {
			return $this->$key();
		}
	}

	public function __construct() {
		add_action( 'admin_menu' , [ $this, 'add_settings_menu' ],8);
		add_action( 'admin_init' , [ $this, 'save_settings' ]);
		add_action( 'admin_init' , [ $this, 'check_license' ],15);
		add_action( 'admin_enqueue_scripts' , [ $this, 'enqueue_styles' ]);
		add_action( 'admin_enqueue_scripts' , [ $this, 'enqueue_scripts' ]);
		$this->plugin_url = plugin_dir_url(GEOTROOT_PLUGIN_FILE) .'vendor/timersys/geot-functions/src/Setting/';
	}
	/**
	 * Register the stylesheets for the Dashboard.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		global $pagenow;

		if( 'post.php' == $pagenow ) {
			wp_enqueue_style('wp-jquery-ui-dialog');
		}
		wp_enqueue_style( 'geot-chosen', $this->plugin_url . 'css/chosen.min.css', array(), null, 'all' );
		wp_enqueue_style( 'geot', $this->plugin_url . 'css/geotarget.css', array(), null, 'all' );

	}

	/**
	 * Register the JavaScript for the dashboard.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( 'geot-chosen', $this->plugin_url . 'js/chosen.jquery.min.js', array( 'jquery' ), null, false );
		wp_enqueue_script( 'geot', $this->plugin_url . 'js/geotargeting-admin.js', array( 'jquery','geot-chosen','jquery-ui-dialog'), null, false );
		wp_localize_script(  'geot', 'geot', array(
			'ajax_url'  => admin_url('admin-ajax.php')
		));
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
		<h3>GeotargetingWP</h3>
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
