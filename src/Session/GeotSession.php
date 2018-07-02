<?php
namespace GeotFunctions\Session;
use WP_Session;
use WP_Session_Utils;

/**
 * GeotSession wrapper Class
 *
 * @since 1.5
 */
class GeotSession {

	// Hold the class instance.
	private static $_instance = null;

	/**
	 * Holds our session data
	 *
	 * @var array
	 * @access private
	 * @since 1.5
	 */
	private $session;

	/**
	 * Session index prefix
	 *
	 * @var string
	 * @access private
	 * @since 2.3
	 */
	private $prefix = '';

	/**
	 * Get things started
	 *
	 * Defines our WP_Session constants, includes the necessary libraries and
	 * retrieves the WP Session instance
	 *
	 * @since 1.5 TODO: UPDATE wp Sessions
	 */
	public function __construct() {

        if( ! $this->should_start_session() ) {
            return;
        }
        // Use WP_Session (default)
        if ( ! defined( 'WP_SESSION_COOKIE' ) ) {
            define( 'WP_SESSION_COOKIE', '_wp_session' );
        }
        if ( ! class_exists( 'Recursive_ArrayAccess' ) ) {
            require_once dirname(__FILE__) . '/wp-session/class-recursive-arrayaccess.php';
        }
        if ( ! class_exists( 'WP_Session' ) ) {
            require_once dirname(__FILE__) . '/wp-session/class-wp-session.php';
            require_once dirname(__FILE__) . '/wp-session/class-wp-session-utils.php';
            require_once dirname(__FILE__) . '/wp-session/wp-session.php';
        }

        // Create the required table.
        add_action('admin_init', [$this, 'create_sm_sessions_table']);
        add_action('wp_session_init', [$this, 'create_sm_sessions_table']);

        if ( empty( $this->session ) )
			add_action( 'plugins_loaded', [ $this, 'init' ], -1 );
	}

	/**
	 * Main GeotSession Instance
	 *
	 * Ensures only one instance is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 *
	 * @return GeotSession - Main instance
	 */
	public static function instance() {
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
	 * Setup the WP_Session instance
	 *
	 * @access public
	 * @since 1.5
	 * @return void
	 */
	public function init() {

		$this->session = WP_Session::get_instance();

		return $this->session;
	}

	/**
	 * Retrieve session ID
	 *
	 * @access public
	 * @since 1.6
	 * @return string Session ID
	 */
	public function get_id() {
		return $this->session->session_id;
	}

	/**
	 * Retrieve a session variable
	 *
	 * @access public
	 * @since 1.5
	 * @param string $key Session key
	 * @return mixed Session variable
	 */
	public function get( $key ) {
		$key    = sanitize_key( $key );

		return json_decode($this->session[ $key ] ) ?: false;
	}

	/**
	 * Set a session variable
	 *
	 * @since 1.5
	 *
	 * @param string $key Session key
	 * @param int|string|array $value Session variable
	 * @return mixed Session variable
	 */
	public function set( $key, $value ) {
		$key = sanitize_key( $key );

		$this->session[ $key ] = wp_json_encode( $value );

		return $this->session[ $key ];
	}

	/**
	 * Determines if we should start sessions
	 *
	 * @since  2.5.11
	 * @return bool
	 */
	public function should_start_session() {
		$start_session = true;
		if( ! empty( $_SERVER[ 'REQUEST_URI' ] ) ) {
			$blacklist = $this->get_blacklist();
			$uri       = ltrim( $_SERVER[ 'REQUEST_URI' ], '/' );
			$uri       = untrailingslashit( $uri );
			if( in_array( $uri, $blacklist ) ) {
				$start_session = false;
			}
			if( false !== strpos( $uri, 'feed=' ) ) {
				$start_session = false;
			}
		}
		if( isset($_GET['page']) && 'geot-debug-data' == $_GET['page'] )
			$start_session = false;

		return apply_filters( 'geot/sessions/start_session', $start_session );
	}

	/**
	 * Retrieve the URI blacklist
	 *
	 * These are the URIs where we never start sessions
	 *
	 * @since  2.5.11
	 * @return array
	 */
	public function get_blacklist() {
		$blacklist = apply_filters( 'geot/sessions/session_start_uri_blacklist', array(
			'feed',
			'feed/rss',
			'feed/rss2',
			'feed/rdf',
			'feed/atom',
			'comments/feed',
		) );
		// Look to see if WordPress is in a sub folder or this is a network site that uses sub folders
		$folder = str_replace( network_home_url(), '', get_site_url() );
		if( ! empty( $folder ) ) {
			foreach( $blacklist as $path ) {
				$blacklist[] = $folder . '/' . $path;
			}
		}
		return $blacklist;
	}

    /**
     * Create the new table for housing session data if we're not still using
     * the legacy options mechanism. This code should be invoked before
     * instantiating the singleton session manager to ensure the table exists
     * before trying to use it.
     *
     * @see https://github.com/ericmann/wp-session-manager/issues/55
     */
    function create_sm_sessions_table() {
        if ( defined( 'WP_SESSION_USE_OPTIONS' ) && WP_SESSION_USE_OPTIONS ) {
            return;
        }

        $current_db_version = '0.1';
        $created_db_version = get_option( 'sm_session_db_version', '0.0' );

        if ( version_compare( $created_db_version, $current_db_version, '<' ) ) {
            global $wpdb;

            $collate = '';
            if ( $wpdb->has_cap( 'collation' ) ) {
                $collate = $wpdb->get_charset_collate();
            }

            $table = "CREATE TABLE {$wpdb->prefix}sm_sessions (
		  session_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		  session_key char(32) NOT NULL,
		  session_value LONGTEXT NOT NULL,
		  session_expiry BIGINT(20) UNSIGNED NOT NULL,
		  PRIMARY KEY  (session_key),
		  UNIQUE KEY session_id (session_id)
		) $collate;";

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            require_once( dirname(__FILE__) .'/wp-session/class-wp-session-utils.php' );
            dbDelta( $table );

            add_option( 'sm_session_db_version', '0.1', '', 'no' );

            WP_Session_Utils::delete_all_sessions_from_options();
        }
    }
}