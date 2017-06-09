<?php
// Don't redefine the functions if included multiple times.
if ( !function_exists('GeotFunctions\toArray') ) {
	require __DIR__ . '/functions.php';
	require __DIR__ . '/filters.php';
	require __DIR__ . '/global-functions.php';
	require __DIR__ . '/database.php';
	require __DIR__ . '/plugins.php';
}