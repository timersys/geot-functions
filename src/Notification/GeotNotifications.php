<?php
namespace GeotFunctions\Notification;

class GeotNotifications {

	/**
	 * Display front end notice to admin
	 * @param $msg
	 */
	public static function notify( $msg ){

			add_action('wp_footer', function() use ($msg) {
				$error = json_decode($msg);
				if( ! current_user_can('administrator') || ! isset( $error->error ) )
					return;
				echo '<div class="geot-alert">
						GeotargetingWP Error: '.esc_html($error->error).'<br/>
						<small>This message it\'s only visible to admins</small></div>';
				self::add_style();
			});
	}
	public static function add_style(){
		?>
		<style type="text/css">
			.geot-alert {
				position: fixed;
				bottom: 0;
				z-index: 999999999;
				background: red;
				width: 100%;
				color: #fff;
				padding: 20px;
				font-size: 12px;
			}</style><?php
	}
}