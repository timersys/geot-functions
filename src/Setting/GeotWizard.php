<?php
namespace GeotFunctions\Setup;

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

		add_action( 'init', [ $this, 'init' ] );
	}


	public function init() {
		if ( apply_filters( 'geot/wizard/enable', true ) && current_user_can( 'manage_options' ) ) {
			add_action( 'admin_menu', [ $this, 'admin_menus' ] );

			if( isset($_GET['page']) && $_GET['page'] == 'geot-setup' ) {

				add_action( 'admin_init', [ $this, 'setup_wizard' ] );
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
	public function setup_wizard() {

		$default_steps = array(
			'basic' => array(
				'name'    => __( 'Basic', 'geot' ),
				'view'    => [ $this, 'setup_wizard_basic' ],
				'handler' => [ $this, 'setup_wizard_basic_save' ],
			),
			'addons'     => array(
				'name'    => __( 'Addons', 'geot' ),
				'view'    => array( $this, 'setup_wizard_addons' ),
				'handler' => array( $this, 'setup_wizard_addons_save' ),
			),
		);
		

		$this->steps = apply_filters( 'geot/wizard/steps', $default_steps );
		$this->step  = isset( $_GET['step'] ) ? sanitize_key( $_GET['step'] ) : current( array_keys( $this->steps ) );

		if ( ! empty( $_POST['save_step'] ) && isset( $this->steps[ $this->step ]['handler'] ) ) {
			call_user_func( $this->steps[ $this->step ]['handler'], $this );
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
		require_once dirname( __FILE__ ) . '/partials/setup-wizard-basic.php';
	}


	/**
	 * Register/enqueue scripts and styles for the Setup Wizard.
	 * Hooked onto 'admin_enqueue_scripts'.
	 */
	public function enqueue_scripts() {

		$version = \GeotFunctions\get_version();
		
		wp_enqueue_style( 'buttons' );
		wp_enqueue_style( 'geot-setup', $this->plugin_url . 'css/wizard.css', array('buttons'), $version, 'all' );
	}
}