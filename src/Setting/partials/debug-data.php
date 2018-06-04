<h2>Geolocation data</h2>
<p>Please copy and paste the whole page if requested by support</p>
<textarea readonly="readonly" onclick="this.focus(); this.select()" id="geot-debug-info">
##Geolocation data##

<?php echo strip_tags(preg_replace('/\t+/', '',geot_debug_data()));?>


##Ip Resolved##

<?php
echo 'server:';
echo '$_SERVER[REMOTE_ADDR] = "'; echo isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : 'not resolved';?>


<?php
echo 'cloudflare:';
echo '$_SERVER[HTTP_CF_CONNECTING_IP] = '; echo isset( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ? $_SERVER['HTTP_CF_CONNECTING_IP'] : 'not resolved';
?>


<?php
echo 'Reblaze:';
echo '$_SERVER[X-Real-IP] = '; echo isset( $_SERVER['X-Real-IP'] ) ? $_SERVER['X-Real-IP'] : 'not resolved';
?>


<?php

echo 'Sucuri:';
echo '$_SERVER[HTTP_X_SUCURI_CLIENTIP] = '; echo isset( $_SERVER['HTTP_X_SUCURI_CLIENTIP'] ) ? $_SERVER['HTTP_X_SUCURI_CLIENTIP'] : 'not resolved';
?>


<?php
echo 'Ezoic:';
echo '$_SERVER[X-FORWARDED-FOR] = '; echo isset( $_SERVER['X-FORWARDED-FOR'] ) ? $_SERVER['X-FORWARDED-FOR'] : 'not resolved';
?>


<?php
echo 'Akamai:';
echo '$_SERVER[True-Client-IP] = '; echo isset( $_SERVER['True-Client-IP'] ) ? $_SERVER['True-Client-IP'] : 'not resolved';
?>


<?php
echo 'Clouways:';
echo '$_SERVER[HTTP_X_FORWARDED_FOR] = '; echo isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : 'not resolved';
?>

<?php
echo 'Wp Engine:';
echo getenv( 'HTTP_GEOIP_COUNTRY_CODE' ) ? 'Yes' : 'No';
if( getenv('HTTP_GEOIP_COUNTRY_CODE')){
    echo "getenv( 'HTTP_GEOIP_CITY' ) :"; echo getenv( 'HTTP_GEOIP_CITY' ) ;
    echo "getenv( 'HTTP_GEOIP_POSTAL_CODE' ) :"; echo getenv( 'HTTP_GEOIP_POSTAL_CODE' ) ;
    echo "getenv( 'HTTP_GEOIP_COUNTRY_CODE' ) :"; echo getenv( 'HTTP_GEOIP_COUNTRY_CODE' ) ;
    echo "getenv( 'HTTP_GEOIP_COUNTRY_NAME' ) :"; echo getenv( 'HTTP_GEOIP_COUNTRY_NAME' ) ;
    echo "getenv( 'HTTP_GEOIP_AREA_CODE' ) :"; echo getenv( 'HTTP_GEOIP_AREA_CODE' ) ;
    echo "getenv( 'HTTP_GEOIP_REGION' ) :"; echo getenv( 'HTTP_GEOIP_REGION' ) ;
    echo "getenv( 'HTTP_GEOIP_LATITUDE' ) :"; echo getenv( 'HTTP_GEOIP_LATITUDE' ) ;
    echo "getenv( 'HTTP_GEOIP_LONGITUDE' ) :"; echo getenv( 'HTTP_GEOIP_LONGITUDE' ) ;
}
?>
</textarea>