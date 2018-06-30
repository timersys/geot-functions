<?php
namespace GeotFunctions;

/**
 * Helper function to convert to array
 * @param  string $value comma separated countries, etc
 * @return array
 */
function toArray( $value = "" ) {
	if ( empty( $value ) )
		return array();

	if ( is_array( $value ) )
		return array_map('trim', $value );

	if ( stripos($value, ',') > 0)
		return array_map( 'trim', explode( ',', $value ) );

	return array( trim( $value ) );
}

/**
 * Convert a one item per line textarea into arrays
 *
 * @param  [type] $string [description]
 *
 * @return array [type]         [description]
 */
function textarea_to_array( $string ) {
	if( ! strlen( trim( $string ) ) )
		return array();
	return toArray ( explode( PHP_EOL, $string ) );
}

/**
 * For backward compatibility we need to use plurals on the keys
 * as they were saved like that on postmeta
 *
 * @param $key
 *
 * @return string
 */
function toPlural ( $key ) {
	switch ( $key ) {
		case 'country' :
			return 'countries';
			break;
		case 'city' :
			return 'cities';
			break;
	}
	return $key;
}

/**
 * Get current post id, to let retrieve from url in case is not set yet
 * changed to grab just to make it clear for me Im not using native wp
 * @return mixed
 */
function grab_post_id(){
	global $post;

	add_filter( 'geot/cancel_posts_where', '__return_true');
	$actual_url = get_current_url();
	$id = isset( $post->ID ) ? $post->ID : url_to_postid($actual_url);
	remove_filter( 'geot/cancel_posts_where', '__return_true');
	return $id;
}

/**
 * Return current url
 * @return string
 */
function get_current_url(){
	return	(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

}

/**
 * Return maxmind db path
 * @return mixed
 */
function maxmind_db(){
	return apply_filters( 'geot/mmdb_path', WP_CONTENT_DIR . '/uploads/geot_plugin/GeoLite2-City.mmdb' );
}

/**
 * Return IP2LOCATION db path
 * @return mixed
 */
function ip2location_db(){
	return apply_filters( 'geot/ip2location_path', WP_CONTENT_DIR . '/uploads/geot_plugin/IP2LOCATION.BIN' );
}

/**
 * Simple filter so plugins can add their own version and bust cache
 * @return mixed
 */
function get_version() {
	return apply_filters( 'geot/plugin_version', '0');
}