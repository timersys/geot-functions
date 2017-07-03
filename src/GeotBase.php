<?php
/**
 * Created by PhpStorm.
 * User: damianlogghe
 * Date: 7/3/17
 * Time: 1:59 PM
 */

namespace GeotFunctions;


class GeotBase {
	public function __construct() {
		require_once dirname(dirname(__FILE__)).'/vendor/autoload.php';
	}
}