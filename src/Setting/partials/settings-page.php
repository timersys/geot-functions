<?php
/**
 * Settings page template
 * @since  1.0.0
 */


$opts     = geot_settings();
$defaults = [
	'license'					=> '',
	'api_secret'				=> '',
	'cache_mode'				=> '0',
	'ajax_mode'					=> '0',
	'debug_mode'				=> '0',
	'var_ip'					=> 'REMOTE_ADDR',
	'maxmind'					=> '0',
	'ip2location'				=> '0',
	'geot_uninstall'			=> '',
	'fallback_country_ips'		=> '',
	'bots_country_ips'			=> '',
];
$opts     = wp_parse_args( $opts, apply_filters( 'geot/default_settings', $defaults ) );

$countries = geot_countries();
?>
<script>
    var geot_countries = <?php echo json_encode( array_map( function ( $a ) {
		return [ 'text' => $a->country, 'value' => $a->iso_code ];
	}, (array) $countries ) );?>;
</script>
<div class="wrap geot-settings">
	<form name="geot-settings" method="post" enctype="multipart/form-data">
		<table class="form-table">
			<?php do_action( 'geot/settings_page/before' ); ?>

			<tr valign="top" class="geot-settings-title">
				<th colspan="2"><h3><?php _e( 'Main settings:', 'geot' ); ?></h3></th>
			</tr>
			<tr valign="top" class="">
				<th><label for="license"><?php _e( 'Enter your API key', 'geot' ); ?></label></th>
				<td colspan="3">
					<label><input type="text" id="license" name="geot_settings[license]"
					              value="<?php echo $opts['license']; ?>" class="api-keys <?php echo 'geot_license_';
						echo ! empty( $opts['license'] ) && get_option( 'geot_license_active' ) ? get_option( 'geot_license_active' ) : ''; ?>"/>
						<button class="button-primary check-license">Check Credits/Subscription</button>
						<p class="help"><?php _e( 'Enter your api key in order to connect with the API and also get automatic updates', 'geot' ); ?></p>
						<?php if ( isset( $_GET['geot_message'] ) ) {
							echo '<p style="color:red;">' . esc_attr( $_GET['geot_message'] ) . '</p>';
						} ?>
				</td>
			</tr>
			<tr valign="top" class="">
				<th><label for="api_secret"><?php _e( 'Enter your API secret', 'geot' ); ?></label></th>
				<td colspan="3">
					<label><input type="password" id="api_secret" name="geot_settings[api_secret]"
					              value="<?php echo $opts['api_secret']; ?>" class="api-keys"/>
						<p class="help"><?php _e( 'Enter your api secret', 'geot' ); ?></p>
				</td>
			</tr>
			<?php if ( file_exists( GeotFunctions\maxmind_db() ) ): ?>
				<tr valign="top" class="">
					<th><label for=""><?php _e( 'Enable Maxmind Database', 'geot' ); ?></label></th>
					<td colspan="3">
						<label><input type="checkbox" id="" name="geot_settings[maxmind]"
						              value="1" <?php checked( $opts['maxmind'], '1' ); ?>/>
							<p class="help"><?php _e( 'Check this if you want to use the database located on:', 'geot' );
								echo ' ' . GeotFunctions\maxmind_db(); ?></p>
					</td>
				</tr>
			<?php endif; ?>

			<?php if ( file_exists( GeotFunctions\ip2location_db() ) ): ?>
				<tr valign="top" class="">
					<th><label for=""><?php _e( 'Enable IP2Location Database', 'geot' ); ?></label></th>
					<td colspan="3">
						<label><input type="checkbox" id="" name="geot_settings[ip2location]"
						              value="1" <?php checked( $opts['ip2location'], '1' ); ?>/>
							<p class="help"><?php _e( 'Check this if you want to use the database located on:', 'geot' );
								echo ' ' . \GeotFunctions\ip2location_db(); ?></p>
					</td>
				</tr>
			<?php endif; ?>

			<?php if ( getenv( 'HTTP_GEOIP_COUNTRY_CODE' ) !== false ): ?>
				<tr valign="top" class="">
					<th><label for=""><?php _e( 'Enable WPEngine Geolocation', 'geot' ); ?></label></th>
					<td colspan="3">
						<label><input type="checkbox" id="" name="geot_settings[wpengine]"
						              value="1" <?php checked( $opts['wpengine'], '1' ); ?>/>
							<p class="help"><?php _e( 'Check this if you want to use WPEngine database', 'geot' ); ?></p>
					</td>
				</tr>
			<?php endif; ?>
			<?php if ( getenv( 'GEOIP_COUNTRY_CODE' ) !== false ): ?>
				<tr valign="top" class="">
					<th><label for=""><?php _e( 'Enable Litespeed Geolocation', 'geot' ); ?></label></th>
					<td colspan="3">
						<label><input type="checkbox" id="" name="geot_settings[litespeed]"
						              value="1" <?php checked( $opts['litespeed'], '1' ); ?>/>
							<p class="help"><?php _e( 'Check this if you want to use Litespeed local database', 'geot' ); ?></p>
					</td>
				</tr>
			<?php endif; ?>

			<?php if ( ! empty( $_SERVER['HTTP_GEOIP_CITY_COUNTRY_NAME'] ) ): ?>
				<tr valign="top" class="">
					<th><label for=""><?php _e( 'Enable Kinsta Geolocation', 'geot' ); ?></label></th>
					<td colspan="3">
						<label><input type="checkbox" id="" name="geot_settings[kinsta]"
						              value="1" <?php checked( $opts['kinsta'], '1' ); ?>/>
							<p class="help"><?php _e( 'Check this if you want to use Kinsta database', 'geot' ); ?></p>
					</td>
				</tr>
			<?php endif; ?>

			<tr valign="top" class="">
				<th><label for=""><?php _e( 'Cache Mode', 'geot' ); ?></label></th>
				<td colspan="3">
					<label><input type="checkbox" id="" name="geot_settings[cache_mode]"
					              value="1" <?php checked( $opts['cache_mode'], '1' ); ?>/>
						<p class="help"><?php echo sprintf( __( 'Check this if you want to save the user location into PHP Sessions. More info <a href="%s">here</a>', 'geot' ), 'https://geotargetingwp.com/docs/geotargeting-pro/configuration#cache' ); ?></p>
				</td>
			</tr>
			

			<tr valign="top" class="">
				<th><label for="region"><?php _e( 'IP', 'geot' ); ?></label></th>
				<td colspan="3">

					<?php if( count($ips) == 0 ) : ?>
						<h3><?php _e('We could not detect any IP', 'geot'); ?></h3>
					<?php elseif( count($ips) == 1 ) : ?>
						<h3 style="font-weight: unset;"><?php printf(__('We detected the following IP : <b>%s</b>', 'geot'), current($ips)); ?></h3>
						<input type="hidden" name="geot_settings[var_ip]" value="<?php echo $opts['var_ip']; ?>" />
					<?php else : ?>

						<select name="geot_settings[var_ip]" class="geot-chosen-select"
						        data-placeholder="<?php _e( 'Choose your IP...', 'geot' ); ?>">

							<?php foreach( $ips as $key => $label_ip ) : ?>
								<option value="<?php echo $key; ?>" <?php echo selected( $key, $opts['fallback_country'] ); ?> ><?php echo $label_ip; ?></option>
							<?php endforeach; ?>

							<?php foreach( $ips as $key => $label_ip ) : ?>
								<option value="<?php echo $key ?>" <?php echo selected( $key, $opts['var_ip'] ); ?>><?php echo $label_ip; ?></option>
							<?php endforeach; ?>
						</select>
						<p class="help"><?php printf(__('If you dont know what IP must choose, you can check your real ip on <a href="%s">%s</a>','geot'), 'https://geotargetingwp.com/ip', 'https://geotargetingwp.com/ip'); ?></p>
					<?php endif; ?>
				</td>
			</tr>

			<tr valign="top" class="">
				<th><label for="region"><?php _e( 'Fallback Country', 'geot' ); ?></label></th>
				<td colspan="3">

					<select name="geot_settings[fallback_country]" class="geot-chosen-select"
					        data-placeholder="<?php _e( 'Type country name...', 'geot' ); ?>">
						<option value=""><?php _e( 'Choose One', 'geot' ); ?></option>
						<?php
						foreach ( $countries as $c ) {
							?>
							<option value="<?php echo $c->iso_code ?>" <?php isset( $opts['fallback_country'] ) ? selected( $c->iso_code, $opts['fallback_country'] ) : ''; ?>> <?php echo $c->country; ?></option>
							<?php
						}
						?>
					</select>

					<p class="help"><?php _e( 'If the user IP is not detected, the plugin will fallback to this country', 'geot' ); ?></p>
				</td>
			</tr>
			<tr valign="top" class="">
				<th><label for="region"><?php _e( 'Fallback Country Whitelisted IPs', 'geot' ); ?></label></th>
				<td colspan="3">
					<textarea rows="10" name="geot_settings[fallback_country_ips]"><?= esc_attr($opts['fallback_country_ips']);?></textarea>
					<p class="help"><?php _e( 'Enter Ip addresses one by line and they will be resolved to the fallback country you choose and won\'t spend requests. You current Ip is: ', 'geot' ); echo \GeotWP\getUserIP(); ?></p>
				</td>
			</tr>
			<tr valign="top" class="">
				<th><label for="bots"><?php _e( 'Bots Country', 'geot' ); ?></label></th>
				<td colspan="3">
					<select name="geot_settings[bots_country]" class="geot-chosen-select"
					        data-placeholder="<?php _e( 'Type country name...', 'geot' ); ?>">
						<option value=""><?php _e( 'Choose One', 'geot' ); ?></option>
						<?php
						foreach ( $countries as $c ) {
							?>
							<option value="<?php echo $c->iso_code ?>" <?php isset( $opts['bots_country'] ) ? selected( $c->iso_code, $opts['bots_country'] ) : ''; ?>> <?php echo $c->country; ?></option>
							<?php
						}
						?>
					</select>

					<p class="help"><?php _e( 'All bots/crawlers will be treated as they were from this country. ', 'geot' ); ?></p>
				</td>
			</tr>
			<tr valign="top" class="">
				<th><label for="region"><?php _e( 'Bots Country IPs', 'geot' ); ?></label></th>
				<td colspan="3">
					<textarea rows="10" name="geot_settings[bots_country_ips]"><?= $opts['bots_country_ips'];?></textarea>
					<p class="help"><?php echo sprintf(__( 'Enter Ip addresses one by line and they will be resolved to the bots country you choose and won\'t spend requests. Check <a href="%s">most queried ips</a> in order to identify bots.', 'geot' ), 'https://geotargetingwp.com/dashboard/stats'); ?></p>
				</td>
			</tr>

			<tr valign="top" class="geot-settings-title">
				<th colspan="3"><h3><?php _e( 'Misc:', 'geot' ); ?></h3></th>
			</tr>
			<tr valign="top">
				<th><h3><?php _e( 'Uninstall:', 'geot' ); ?></h3></th>
				<td colspan="3">
					<p><?php _e( 'Check this if you want to <strong>delete all plugin data</strong> on uninstall', 'geot' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'geot/settings_page/after' ); ?>
			<tr valign="top" class="">
				<th><label for=""><?php _e( 'Uninstall', 'geot' ); ?></label></th>
				<td colspan="3">
					<input type="checkbox" id="" name="geot_settings[geot_uninstall]"
					       value="1" <?php checked( $opts['geot_uninstall'], '1' ); ?>/>
					<p class="help"><?php _e( 'Will delete all database records and plugin settings when you delete the plugin', 'geot' ); ?></p>
				</td>
			</tr>

			<tr valign="top" class="">
				<th><h3><?php _e( 'Export/import:', 'geot' ); ?></h3></th>
				<td colspan="3">
					<p><?php _e( 'Export your setting or import them with a few clicks', 'geot' ); ?></p>
				</td>
			</tr>
			<tr valign="top" class="">
				<th><label for=""><?php _e( 'Export settings', 'geot' ); ?></label></th>
				<td colspan="3">
					<div id="export_href">

					</div>
					<script type="text/javascript">
                        var geot_settings = '<?php echo json_encode( $opts );?>';
                        var data = "text/json;charset=utf-8," + encodeURIComponent(geot_settings);
                        jQuery('<a href="data:' + data + '" download="geot_settings.json" class="button">Export Settings</a>').appendTo('#export_href');
					</script>
				</td>
			</tr>
			<tr valign="top" class="">
				<th><label for=""><?php _e( 'Import settings', 'geot' ); ?></label></th>
				<td colspan="3">
					Select image to upload:
					<input type="file" name="geot_settings_json" id="fileToUpload"><br/>
					<input type="submit" value="Import" name="submit">
				</td>
			</tr>

			<tr>
				<td><input type="submit" class="button-primary" value="<?php _e( 'Save settings', 'geot' ); ?>"/></td>
				<?php wp_nonce_field( 'geot_save_settings', 'geot_nonce' ); ?>
		</table>
	</form>
</div>
