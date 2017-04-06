<?php
namespace GeotFunctions\Notification;

class GeotNotifications {

	/**
	 * Display front end notice to admin
	 * @param $msg
	 */
	public static function notify( $msg ){
		if( current_user_can('administrator')){
			add_action('wp_footer', function() use ($msg) {
				echo '<div class="geot-alert">
						<p>'.esc_html($msg).'</p>
						<p><small>This message it\'s only visible to admins</small></p></div>';
			});
		}
	}
}