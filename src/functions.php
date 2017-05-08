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