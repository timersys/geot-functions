<?php namespace GeotFunctions;
use GeotFunctions\Email\GeotEmails;
use GeotWP;
use GeotWP\Exception\InvalidLicenseException;
use GeotWP\Exception\OutofCreditsException;
use GeotWP\GeotargetingWP;
use GeotWP\Record\GeotRecord;

class GeotFunctions {
	// Hold the class instance.
	private static $_instance = null;

	/**
	 * Current user country and cityused everywhere
	 * @var string
	 */
	protected $userData = null;
	protected $userCountry;
	protected $userCity;
	protected $userState;
	protected $userContinent;
	/**
	 * Plugin settings
	 * @var array
	 */
	protected $opts;
	public $geotWP;


	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	private function __construct( ) {

		$this->opts = geot_settings();

		$args = apply_filters('geotWP/args', array() );
		// don't go further without license 
		if( empty( $this->opts['license'] ) )
			return;

		$this->geotWP = new GeotargetingWP( $this->opts['license'], $args );

		if( !is_admin()
		    && ! defined('DOING_CRON')
		    && ! defined('DOING_AJAX') )
			add_action('init' , array($this,'setUserData' ) );
	}

	/**
	 * Main Geot Instance
	 *
	 * Ensures only one instance of WSI is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see GEOT()
	 * @return Geot - Main instance
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
	 * Run after init
	 * @since  1.0.1
	 */
	public function setUserData() {
		if( $this->userData )
			return apply_filters('geot/user_data', $this->userData );

		$this->userData = $this->getUserData();

		return  apply_filters('geot/user_data', $this->userData);
	}

	/**
	 * @param string $ip
	 *
	 * @return array|bool|mixed
	 */
	public function getUserData( $ip = "" ){

		try{
			$data = $this->geotWP->getData($ip);
		} catch ( OutofCreditsException $e ) {
			GeotEmails::OutOfQueriesException();
			return $this->getFallbackCountry();
		} catch ( InvalidLicenseException $e ) {
			GeotEmails::AuthenticationException();
			return $this->getFallbackCountry();
		} catch ( \Exception $e ) {
			echo '<pre>';
			var_dump($e);
			echo '</pre>';die();
			return $this->getFallbackCountry();
		}
		return $data;
	}
	/**
	 * Get a specif record
	 * @param $key
	 *
	 * @return mixed
	 */
	public function get( $key ) {
		if( !in_array( $key, GeotRecord::getValidRecords() ) )
			return 'Invalid GeotRecord classname provided. Valids ones are: '. implode(',', GeotRecord::getValidRecords());

		if( $this->userData === null )
			$this->setUserData();

		if( isset( $this->userData->$key ) )
			return $this->userData->$key;
		return false;
	}


	/**
	 * Get country from database and return object like api
	 * @param $iso_code
	 *
	 * @return StdClass
	 */
	private function getCountryByIsoCode( $iso_code ) {
		global $wpdb;
		$query 	 = "SELECT * FROM {$wpdb->base_prefix}geot_countries WHERE iso_code = %s";
		$result = $wpdb->get_row( $wpdb->prepare($query, array( $iso_code )), ARRAY_A );
		$country = new \StdClass();

		$country->name      = $result['country'];
		$country->iso_code  = $result['iso_code'];

		return $country;
	}

	/**
	 * If we have a maxmind exception, return
	 * @return array|bool
	 */
	private function getFallbackCountry() {

		if( empty($this->opts['fallback_country']) )
			$this->opts['fallback_country'] = 'US';

		return array(
			'country' => $this->getCountryByIsoCode( $this->opts['fallback_country'] ),
			'city'    => '',
			'zip'     => '',
			'state'   => '',
			'continent'   => '',
		);

	}

	/**
	 * Bots can be treated as all from one country
	 * @return array
	 */
	private function getBotsCountry() {

		return apply_filters( 'geot/bots_country', array(
			'country' => $this->getCountryByIsoCode($this->opts['bots_country']),
			'city'    => '',
			'zip'     => '',
			'state'   => '',
		));

	}

	/**
	 * Main function that return is user targeted for
	 *
	 * @param $key city, continent, country, state
	 * @param array $args include, exclude, region, exclude_region
	 *
	 * @return bool
	 */
	public function target( $key, $args = [] ) {
		//Push places list into array
		$places         = toArray( $args['include'] );
		$exclude_places = toArray( $args['exclude'] );
		$saved_regions  = apply_filters( 'geot/get_' . $key . 'regions', array() );
		$plural_key     = toPlural( $key );

		//Append any regions
		if ( ! empty( $args['region'] ) && ! empty( $saved_regions ) ) {
			$region = toArray( $args['region'] );
			foreach ( $region as $region_name ) {
				foreach ( $saved_regions as $key => $saved_region ) {
					if ( strtolower( $region_name ) == strtolower( $saved_region['name'] ) ) {
						$places = array_merge( (array) $places, (array) $saved_region[ $plural_key ] );
					}
				}
			}
		}
		// append excluded regions to excluded places
		if ( ! empty( $args['exclude_region'] ) && ! empty( $saved_regions ) ) {
			$exclude_region = toArray( $args['exclude_region'] );
			foreach ( $exclude_region as $region_name ) {
				foreach ( $saved_regions as $key => $saved_region ) {
					if ( strtolower( $region_name ) == strtolower( $saved_region['name'] ) ) {
						$exclude_places = array_merge( (array) $exclude_places, (array) $saved_region[ $plural_key ] );
					}
				}
			}
		}

		//set target to false
		$target     = false;
		$user_place = $this->get( $key );
		if ( ! $user_place ) {
			return apply_filters( 'geot/target_' . $key . '/return_on_user_null', false );
		}

		if ( count( $places ) > 0 ) {
			foreach ( $places as $p ) {
				if ( strtolower( $user_place->name() ) == strtolower( $p ) || strtolower( $user_place->iso_code() ) == strtolower( $p ) ) {
					$target = true;
				}
			}
		} else {
			// If we don't have places to target return true
			$target = true;
		}

		if ( count( $exclude_places ) > 0 ) {
			foreach ( $exclude_places as $ep ) {
				if ( strtolower( $user_place->name() ) == strtolower( $ep ) || strtolower( $user_place->iso_code() ) == strtolower( $ep ) ) {
					$target = false;
				}
			}
		}
		return $target;
	}


}
