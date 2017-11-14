<?php
namespace GeotFunctions\Session;
use WP_Session;

/**
 * GeotSession wrapper Class
 *
 * @since 1.5
 */
class GeotSession {

	/**
	 * Holds our session data
	 *
	 * @var array
	 * @access private
	 * @since 1.5
	 */
	private $session;

	/**
	 * Whether to use PHP $_SESSION or WP_Session
	 *
	 * @var bool
	 * @access private
	 * @since 1.5,1
	 */
	private $use_php_sessions = false;

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
	 * @since 1.5
	 */
	public function __construct() {

			if( ! $this->should_start_session() ) {
				return;
			}
			// Use WP_Session (default)
			if ( ! defined( 'WP_SESSION_COOKIE' ) ) {
				define( 'WP_SESSION_COOKIE', 'geot_wp_session' );
			}
			if ( ! class_exists( 'Recursive_ArrayAccess' ) ) {
				require_once dirname(__FILE__) . '/wp-session/class-recursive-arrayaccess.php';
			}
			if ( ! class_exists( 'WP_Session' ) ) {
				require_once dirname(__FILE__) . '/wp-session/class-wp-session.php';
				require_once dirname(__FILE__) . '/wp-session/class-wp-session-utils.php';
				require_once dirname(__FILE__) . '/wp-session/wp-session.php';
			}

		if ( empty( $this->session ) )
			add_action( 'plugins_loaded', [ $this, 'init' ], -1 );
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
}