<?php

namespace GeotFunctions\Setting;

use GeotFunctions\Setting\GeotSettings;
use GeotWP\GeotargetingWP;

class GeotWizard {

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

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		add_action( 'init', [ $this, 'setup' ] );
	}


	public function setup() {
		if ( apply_filters( 'geot/wizard/enable', true ) && current_user_can( 'manage_options' ) ) {

			add_action( 'admin_menu', [ $this, 'admin_menus' ] );

			if( isset($_GET['page']) && $_GET['page'] == 'geot-setup' ) {

				add_action( 'admin_init', [ $this, 'wizard' ] );
				add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
			}
		}

		$this->plugin_url = plugin_dir_url( GEOTROOT_PLUGIN_FILE ) . 'vendor/timersys/geot-functions/src/Setting/';
	}


	/**
	 * Add admin menus/screens.
	 */
	public function admin_menus() {
		add_dashboard_page( '', '', 'manage_options', 'geot-setup', '' );
	}


	/**
	 * Show the setup wizard.
	 */
	public function wizard() {

		$default_steps = array(
				'basic' => array(
					'name'    => __( 'Basic', 'geot' ),
					'view'    => [ $this, 'setup_wizard_basic' ],
					'handler' => [ $this, 'setup_wizard_basic_save' ],
				),
			);


		$this->steps = apply_filters( 'geot/wizard/steps', $default_steps );
		$this->step  = isset( $_GET['step'] ) ? sanitize_key( $_GET['step'] ) : current( array_keys( $this->steps ) );

		if ( ! empty( $_POST['save_step'] ) && isset( $this->steps[ $this->step ]['handler'] ) ) {
			call_user_func( $this->steps[ $this->step ]['handler'], $this );

			wp_safe_redirect( esc_url_raw( $this->get_next_step_link() ) );
			exit();
		}

		// @codingStandardsIgnoreEnd
		ob_start();
		set_current_screen();
		$this->setup_wizard_header();
		$this->setup_wizard_steps();
		$this->setup_wizard_content();
		$this->setup_wizard_footer();
		exit;
	}


	/**
	 * Setup Wizard Header.
	 */
	public function setup_wizard_header() {
		require_once dirname( __FILE__ ) . '/partials/setup-wizard-header.php';
	}

	/**
	 * Setup Wizard Footer.
	 */
	public function setup_wizard_footer() {
		require_once dirname( __FILE__ ) . '/partials/setup-wizard-footer.php';
	}

	/**
	 * Output the steps.
	 */
	public function setup_wizard_steps() {
		$step_all 		= $this->steps;
		$step_current 	= $this->step;

		require_once dirname( __FILE__ ) . '/partials/setup-wizard-steps.php';
	}

	/**
	 * Output the content for the current step.
	 */
	public function setup_wizard_content() {
		if ( ! empty( $this->steps[ $this->step ]['view'] ) ) {
			call_user_func( $this->steps[ $this->step ]['view'], $this );
		}
	}


	public function setup_wizard_basic() {

		$opts     = geot_settings();
		$defaults = [
			'license'			=> '',
			'api_secret'		=> '',
			'fallback_country'	=> '',
			'bots_country'		=> '',
		];
		$opts = wp_parse_args( $opts, apply_filters( 'geot/default_settings', $defaults ) );

		$countries = geot_countries();

		$ips = array();

		if( isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR']) &&
			!in_array($_SERVER['REMOTE_ADDR'], $ips) ) {
			$ips[] = $_SERVER[ 'REMOTE_ADDR' ];
		}

		if( isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR']) &&
			!in_array($_SERVER['HTTP_X_FORWARDED_FOR'], $ips) ) {
			$ips[] = $_SERVER[ 'HTTP_X_FORWARDED_FOR' ];
		}

		if( isset($_SERVER['HTTP_CLIENT_IP']) && !empty($_SERVER['HTTP_CLIENT_IP']) &&
			!in_array($_SERVER['HTTP_CLIENT_IP'], $ips) ) {
			$ips[] = $_SERVER[ 'HTTP_CLIENT_IP' ];
		}

		if( isset($_SERVER['HTTP_X_REAL_IP']) && !empty($_SERVER['HTTP_X_REAL_IP']) &&
			!in_array($_SERVER['HTTP_X_REAL_IP'], $ips) ) {
			$ips[] = $_SERVER[ 'HTTP_X_REAL_IP' ];
		}

		require_once dirname( __FILE__ ) . '/partials/setup-wizard-basic.php';
	}


	public function setup_wizard_basic_save() {
		check_admin_referer( 'geot-setup' );

		$settings = array_map('esc_html', $_POST['geot_settings']);

		// update license field
		if ( ! empty( $settings['license'] ) ) {
			$license = esc_attr( $settings['license'] );
			$this->is_valid_license( $license );
		}

		// old settings
		$old_settings = geot_settings();
		
		// checkboxes dirty hack
		$inputs = [
			'license',
			'api_secret',
			'fallback_country',
			'bots_country',
		];

		foreach ($inputs as $input ) {
			if( ! isset($settings[$input]) || empty($settings[$input]) ) {
				$settings[$input] = '';
			}
		}

		if( is_array( $old_settings ) ) {
			$settings = array_merge( $old_settings, $settings );
		}
		
		update_option( 'geot_settings', $settings );
	}


	/**
	 * Register/enqueue scripts and styles for the Setup Wizard.
	 * Hooked onto 'admin_enqueue_scripts'.
	 */
	public function enqueue_scripts() {

		$version = \GeotFunctions\get_version();
		
		wp_enqueue_style( 'buttons' );
		wp_enqueue_style( 'geot-setup', $this->plugin_url . 'css/wizard.css', array('buttons'), $version, 'all' );

		wp_enqueue_script( 'geot-selectize', $this->plugin_url . 'js/selectize.min.js', array( 'jquery' ), $version, false );
		wp_enqueue_script( 'geot-chosen', $this->plugin_url . 'js/chosen.jquery.min.js', array( 'jquery' ), $version, false );
	}


	/**
	 * Get the URL for the next step's screen.
	 *
	 * @param string $step  slug (default: current step).
	 * @return string       URL for next step if a next step exists.
	 *                      Admin URL if it's the last step.
	 *                      Empty string on failure.
	 * @since 1.0.0
	 */
	public function get_next_step_link( $step = '' ) {
		
		if ( ! $step ) {
			$step = $this->step;
		}

		$keys = array_keys( $this->steps );
		if ( end( $keys ) === $step ) {
			return admin_url('admin.php?page=geot-settings&view=general');
		}

		$step_index = array_search( $step, $keys, true );
		if ( false === $step_index ) {
			return '';
		}

		return add_query_arg( 'step', $keys[ $step_index + 1 ], remove_query_arg( 'activate_error' ) );
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
}