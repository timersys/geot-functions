<?php
/**
 * Different helpers filters used around
 */

add_filter( 'geot/get_country_regions', function (){
	$settings   = geot_settings();
	
	if( isset( $settings['region'] ) )
		$regions = array_merge($settings['region'], geot_predefined_regions());
	else
		$regions = geot_predefined_regions();

	return $regions;
} );

add_filter( 'geot/get_city_regions', function () {
	$settings = geot_settings();
	$regions  = isset( $settings['city_region'] ) ? $settings['city_region'] : array();

	return $regions;
} );

add_filter( 'geot/get_countries', function () {
	$countries = wp_cache_get( 'geot_countries' );
	if ( false === $countries ) {
		global $wpdb;

		$countries = $wpdb->get_results( "SELECT iso_code, country FROM {$wpdb->base_prefix}geot_countries ORDER BY country" );

		wp_cache_set( 'geot_countries', $countries );
	}

	return $countries;
} );