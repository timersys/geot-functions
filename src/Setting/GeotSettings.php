<?php

namespace GeotFunctions\Setting;

use GeotWP\GeotargetingWP;

class GeotSettings {

	/**
	 * Plugin Instance
	 * @since 1.0.0
	 * @var The Fbl plugin instance
	 */
	protected static $_instance = null;

	/**
	 * Current view inside settings
	 * @var string
	 */
	private $view;

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
	 *
	 * @param mixed $key
	 *
	 * @since 1.0.0
	 * @return mixed
	 */
	public function __get( $key ) {
		if ( in_array( $key, array( 'payment_gateways', 'shipping', 'mailer', 'checkout' ) ) ) {
			return $this->$key();
		}
	}

	public function __construct() {

		add_action( 'admin_menu', [ $this, 'add_settings_menu' ], 8 );
		add_action( 'admin_init', [ $this, 'check_license' ], 15 );
		add_action( 'wp_ajax_geot_check_license', [ $this, 'ajax_check_license' ] );
		add_action( 'wp_ajax_geot_cities_by_country', [ $this, 'geot_cities_by_country' ] );

		$this->plugin_url = plugin_dir_url( GEOTROOT_PLUGIN_FILE ) . 'vendor/timersys/geot-functions/src/Setting/';

		// Check what page we are on.
		$page = isset( $_GET['page'] ) ? $_GET['page'] : '';

		// Only load if we are actually on the settings page.
		if ( 'geot-settings' === $page ) {
			// trigger settings save
			add_action( 'admin_init', [ $this, 'save_settings' ] );

			// Determine the current active settings tab.
			$this->view = isset( $_GET['view'] ) ? esc_html( $_GET['view'] ) : 'general';

			// add general settings panel
			add_action('geot/settings_general_panel', [ $this, 'output'] );

		}

		// load assets globally as used by child plugins
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_styles' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

	}

	/**
	 * Register the stylesheets for the Dashboard.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		global $pagenow;

		if ( 'post.php' == $pagenow ) {
			wp_enqueue_style( 'wp-jquery-ui-dialog' );
		}
		$version = \GeotFunctions\get_version();
		wp_enqueue_style( 'geot', $this->plugin_url . 'css/geotarget.css', array(), $version, 'all' );

	}

	/**
	 * Register the JavaScript for the dashboard.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		$version = \GeotFunctions\get_version();
		wp_enqueue_script( 'geot-selectize', $this->plugin_url . 'js/selectize.min.js', array( 'jquery' ), $version, false );
		wp_enqueue_script( 'geot-chosen', $this->plugin_url . 'js/chosen.jquery.min.js', array( 'jquery' ), $version, false );
		wp_enqueue_script( 'geot', $this->plugin_url . 'js/geotargeting-admin.js', array(
			'jquery',
			'geot-chosen',
			'geot-selectize',
			'jquery-ui-dialog'
		), $version, false );
		wp_localize_script( 'geot', 'geot', array(
			'ajax_url' => admin_url( 'admin-ajax.php' )
		) );
	}

	/**
	 * Check for license or show notice
	 */
	public function check_license() {
		$opts = geot_settings();
		if ( empty( $opts['license'] ) || empty( $opts['api_secret'] ) ) {
			add_action( 'admin_notices', [ $this, 'license_missing_notice' ], 10 );
		}
	}

	/**
	 * Ajax callback for check license button
	 */
	public function ajax_check_license() {
		if ( empty( $_POST['license'] ) ) {
			echo json_encode( [ 'error' => 'Please enter the license' ] );
			die();
		}
		$license  = esc_attr( $_POST['license'] );
		$response = $this->is_valid_license( $license );

		$opts            = geot_settings();
		$opts['license'] = $license;

		update_option( 'geot_settings', $opts );
		echo $response; // send result to javascript
		die();
	}

	/**
	 * Call the API and update if valid license
	 * Return original response for later use
	 *
	 * @param $license
	 *
	 * @return mixed
	 */
	function is_valid_license( $license ) {
		try {
			$response = GeotargetingWP::checkLicense( $license );
			$result   = json_decode( $response );
			// update license
			if ( isset( $result->success ) ) {
				update_option( 'geot_license_active', 'valid' );
			} else {
				delete_option( 'geot_license_active' );
			}
		} catch ( \Exception $e ) {
			return \GuzzleHttp\json_encode( [ 'error' => $e->getMessage() ] );
		}

		return $response;
	}

	/**
	 * License missing message
	 */
	public function license_missing_notice() {
		?>
		<div class="notice notice-error">
		<h3>GeotargetingWP</h3>
		<p><?php __( printf( 'In order to to use the plugin you need to enter the api keys in the <a href="%1$s">settings page</a>.', admin_url( 'admin.php?page=geot-settings' ), 'geot' ) ); ?></p>
		</div>
		<?php
	}

	/**
	 * Add menu for Settings page of the plugin
	 * @since  1.0.0
	 * @return  void
	 */
	public function add_settings_menu() {

		add_menu_page( 'GeoTargetingWP', 'GeoTargetingWP', apply_filters( 'geot/settings_page_role', 'manage_options' ), 'geot-settings', array(
			$this,
			'settings_page'
		), 'dashicons-admin-site' );
		add_submenu_page( 'geot-settings', 'Settings', 'Settings', apply_filters( 'geot/settings_page_role', 'manage_options' ), 'geot-settings', array(
			$this,
			'settings_page'
		) );
		add_submenu_page( 'geot-settings', 'Debug data', 'Debug data', apply_filters( 'geot/settings_page_role', 'manage_options' ), 'geot-debug-data', array(
			$this,
			'debug_data_page'
		) );
	}

	/**
	 * Return registered settings tabs.
	 *
	 * @return array
	 */
	public function get_tabs() {

		$tabs = [
			'general' => [
				'name'   => esc_html__( 'General', 'popups' ),
			],
		];

		return apply_filters( 'geot/settings_tabs', $tabs );
	}

	/**
	 * Output tab navigation area.
	 */
	public function tabs() {

		$tabs = $this->get_tabs();

		echo '<ul class="geot-admin-tabs">';
		foreach ( $tabs as $id => $tab ) {

			$active = $id === $this->view ? 'active' : '';
			$name   = $tab['name'];
			$link   = add_query_arg( 'view', $id, admin_url( 'admin.php?page=geot-settings' ) );
			echo '<li><a href="' . esc_url_raw( $link ) . '" class="' . esc_attr( $active ) . '">' . esc_html( $name ) . '</a></li>';
		}
		echo '</ul>';
	}

	/**
	 * Build the output for the plugin settings page.
	 *
	 * @since 1.0.0
	 */
	public function output() {
		include dirname( __FILE__ ) . '/partials/settings-page.php';
	}
	/**
	 * Settings page for plugin
	 * @since 1.0.0
	 */
	public function settings_page() {
		?>
		<h2>GeoTargetingWP</h2>
		<div id="geot-settings" class="wrap geot-admin-wrap">
			<?php $this->tabs(); ?>
			<?php do_action("geot/settings_{$this->view}_panel") ?>
		</div><?php
	}

	/**
	 * Save the settings page
	 * @since 1.0.0
	 * @return void
	 */
	public function save_settings() {

		if ( isset( $_POST['geot_nonce'] ) && wp_verify_nonce( $_POST['geot_nonce'], 'geot_save_settings' ) ) {
			$settings = esc_sql( $_POST['geot_settings'] );
			if ( isset( $_FILES['geot_settings_json'] ) && 'application/json' == $_FILES['geot_settings_json']['type'] ) {
				$file     = file_get_contents( $_FILES['geot_settings_json']['tmp_name'] );
				$settings = json_decode( $file, true );

			}
			// trim fields
			if ( is_array( $settings ) ) {
				$settings = array_filter( $settings, function ( $a ) {
					if ( is_string( $a ) ) {
						return trim( $a );
					}

					return $a;
				} );
			}
			// update license field
			if ( ! empty( $settings['license'] ) ) {
				$license = esc_attr( $settings['license'] );
				$this->is_valid_license( $license );
			}

			update_option( 'geot_settings', $settings );
		}
	}

	/*
	 * Get a country code and return cities
	 */
	public function geot_cities_by_country() {
		global $wpdb;

		if ( empty( $_POST['country'] ) ) {
			die();
		}

		$cities = geot_get_cities( $_POST['country'] );
		$json   = [];
		if ( ! empty( $cities ) ) {
			$cities = json_decode( $cities );

			foreach ( $cities as $c ) {
				$json[] = [ 'name' => $c->city ];
			}
		}
		echo json_encode( $json );
		die();
	}

	/**
	 * Debug Data page
	 */
	public function debug_data_page() {
		include dirname( __FILE__ ) . '/partials/debug-data.php';
	}
}
