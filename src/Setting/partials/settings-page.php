<?php
/**
 * Settings page template
 * @since  1.0.0
 */


$opts = geot_settings();
$defaults = [
	'license'                   => '',
	'api_secret'                => '',
	'region'                    => [['name','countries']],
	'city_region'               => [['name','cities']],
	'cache_mode'                => '0',
	'ajax_mode'                 => '0',
	'debug_mode'                => '0',
	'disable_menu_integration'  => '0',
	'disable_widget_integration'=> '0',
	'geot_uninstall'            => '',
];
$opts = wp_parse_args( $opts, apply_filters ('geot/default_settings', $defaults ) );

$countries 	= geot_countries();

?>
<div class="wrap geot-settings">
	<h2>GeoTargetingWP</h2>
	<form name="geot-settings" method="post" enctype="multipart/form-data">
		<table class="form-table">
			<?php do_action( 'geot/settings_page/before' ); ?>

			<tr valign="top" class="">
				<th><h3><?php _e( 'Main settings:', 'geot'); ?></h3></th>
			</tr>
			<tr valign="top" class="">
				<th><label for="license"><?php _e( 'Enter your API key', 'geot'); ?></label></th>
				<td colspan="3">
					<label><input type="text" id="license" name="geot_settings[license]" value="<?php  echo $opts['license'];?>" class="api-keys <?php echo 'geot_license_' ; echo !empty($opts['license']) && get_option( 'geot_license_active' ) ? get_option( 'geot_license_active' ) :'';?>" /><button class="button-primary check-license">Check license</button>
					<p class="help"><?php _e( 'Enter your api key in order to connect with the API and also get automatic updates', 'geot'); ?></p>
                    <?php if( isset($_GET['geot_message']) )
                        echo '<p style="color:red;">'.esc_attr($_GET['geot_message']).'</p>';?>
				</td>
			</tr>
			<tr valign="top" class="">
				<th><label for="api_secret"><?php _e( 'Enter your API secret', 'geot'); ?></label></th>
				<td colspan="3">
					<label><input type="password" id="api_secret" name="geot_settings[api_secret]" value="<?php  echo $opts['api_secret'];?>" class="api-keys" />
					<p class="help"><?php _e( 'Enter your api secret', 'geot'); ?></p>
				</td>
			</tr>
			<tr valign="top" class="">
				<th><label for=""><?php _e( 'Cache Mode', 'geot'); ?></label></th>
				<td colspan="3">
					<label><input type="checkbox" id="" name="geot_settings[cache_mode]" value="1" <?php checked($opts['cache_mode'],'1');?>/>
						<p class="help"><?php _e( 'Check this if you want to save the user location into PHP Sessions', 'geot'); ?></p>
				</td>
			</tr>
			<tr valign="top" class="">
				<th><label for=""><?php _e( 'Debug Mode', 'geot'); ?></label></th>
				<td colspan="3">
					<label><input type="checkbox" id="" name="geot_settings[debug_mode]" value="1" <?php checked($opts['debug_mode'],'1');?>/>
						<p class="help"><?php _e( 'Check this if you want to print in the html code some debug info.', 'geot'); ?></p>
				</td>
			</tr>

			<tr valign="top" class="">
				<th><label for="region"><?php _e( 'Fallback Country', 'geot'); ?></label></th>
				<td colspan="3">

					<select name="geot_settings[fallback_country]"  class="geot-chosen-select" data-placeholder="<?php _e('Type country name...', 'geot');?>" >
						<option value=""><?php _e( 'Choose One', 'geot');?></option>
						<?php
						foreach ($countries as $c) {
							?>
							<option value="<?php echo $c->iso_code?>" <?php isset( $opts['fallback_country'] ) ? selected( $c->iso_code, $opts['fallback_country'] ) : ''; ?>> <?php echo $c->country; ?></option>
							<?php
						}
						?>
					</select>

					<p class="help"><?php _e( 'If the user IP is not detected plugin will fallback to this country', 'geot' ); ?></p>
				</td>

			</tr>
			<tr valign="top" class="">
				<th><label for="bots"><?php _e( 'Bots Country', 'geot' ); ?></label></th>
				<td colspan="3">
					<select name="geot_settings[bots_country]"  class="geot-chosen-select" data-placeholder="<?php _e('Type country name...', 'geot' );?>" >
						<option value=""><?php _e( 'Choose One', 'geot' );?></option>
						<?php
						foreach ($countries as $c) {
							?>
							<option value="<?php echo $c->iso_code?>" <?php isset( $opts['bots_country'] ) ? selected( $c->iso_code, $opts['bots_country'] ) :''; ?>> <?php echo $c->country; ?></option>
							<?php
						}
						?>
					</select>

					<p class="help"><?php _e( 'All bots / crawlers will be treated as the are from this country. More info in ', 'geot' ); ?><a href="https://timersys.com/geotargeting/docs/bots-seo/">Bots in Geotargeting</a></p>
				</td>
			</tr>

			<tr valign="top" class="">
				<th><h3><?php _e( 'Countries:', 'geot' ); ?></h3></th>
				<td colspan="3">
				</td>
			</tr>
			<tr valign="top" class="">
				<th><label for="region"><?php _e( 'Create new region', 'geot' ); ?></label></th>
				<td colspan="3">
				<?php

				if( !empty( $opts['region'] ) ) {
					$i = 0;
					foreach ( $opts['region'] as $region ) { $i++;?>

						<div class="region-group"  data-id="<?php echo $i;?>" >

							<input type="text" placeholder="Enter region name" name="geot_settings[region][<?php echo $i;?>][name]" value="<?php echo !empty( $region['name'] )? esc_attr($region['name']): '' ; ?>"/>
							<a href="#" class="remove-region"title="<?php _e( 'Remove Region', 'geot' );?>">-</a>
							<select name="geot_settings[region][<?php echo $i;?>][countries][]" multiple class="geot-chosen-select" data-placeholder="<?php _e('Type country name...', 'geot' );?>" >
								<?php
									foreach ($countries as $c) {
										?>
										<option value="<?php echo $c->iso_code?>" <?php isset( $region['countries'] ) && is_array( $region['countries'] ) ? selected(true, in_array( $c->iso_code, $region['countries']) ) :''; ?>> <?php echo $c->country; ?></option>
										<?php
									}
								?>
							</select>

						</div>
					<?php }
				}?>
					<a href="#" class="add-region button">Add Region</a>
					<p class="help"><?php _e( 'Add as many countries you need for each region', 'geot' ); ?></p>
				</td>

			</tr>
			<tr valign="top" class="">
				<th><h3><?php _e( 'Cities:', 'geot' ); ?></h3></th>
				<td colspan="3">
				</td>
			</tr>
			<tr valign="top" class="">
				<th><label for="region"><?php _e( 'Create new region', 'geot' ); ?></label></th>
				<td colspan="3">
				<?php

				if( !empty( $opts['city_region'] ) ) {
					$j = 0;
					foreach ( $opts['city_region'] as $city_region ) { $j++;?>

						<div class="city-region-group"  data-id="<?php echo $j;?>" >
							<input type="text" placeholder="Enter region name" name="geot_settings[city_region][<?php echo $j;?>][name]" value="<?php echo !empty( $city_region['name'] )? esc_attr($city_region['name']): '' ; ?>"/>
							<select name="geot_settings[city_region][<?php echo $j;?>][countries][]"  class="geot-chosen-select country_ajax" data-counter="<?php echo $j;?>" data-placeholder="<?php _e('Type country name...', 'geot' );?>" >
								<option value=""><?php _e('Choose a Country', 'geot' );?></option>
								<?php
								foreach ($countries as $c) {
									?>
									<option value="<?php echo $c->iso_code?>" <?php isset( $city_region['countries'] ) && is_array( $city_region['countries'] ) ? selected(true, in_array( $c->iso_code, $city_region['countries']) ):''; ?>> <?php echo $c->country; ?></option>
								<?php
								}
								?>
							</select>
							<a href="#" class="remove-city-region"title="<?php _e( 'Remove Region', 'geot' );?>">-</a>
							<select name="geot_settings[city_region][<?php echo $j;?>][cities][]" multiple class="geot-chosen-select cities_container" id="<?php echo 'cities'.$j;?>" data-placeholder="<?php _e('First choose a country', 'geot' );?>" >
								<?php
								if( !empty($city_region['countries'])) {
									$cities = json_decode( geot_get_cities( $city_region['countries'][0] ) );
									if( $cities ) {
										foreach ( $cities as $c ) {
											?>
											<option
													value="<?php echo strtolower( $c->city ) ?>" <?php isset( $city_region['cities'] ) && is_array( $city_region['cities'] ) ? selected( true, in_array( strtolower( $c->city ), $city_region['cities'] ) ) : ''; ?>> <?php echo $c->city; ?></option>
											<?php
										}
									}
								}
								?>
							</select>
						</div>
					<?php }
				}?>
					<a href="#" class="add-city-region button">Add City Region</a>
					<p class="help"><?php _e( 'Add as many cities you need for each region', 'geot' ); ?></p>
				</td>

			</tr>
			<tr valign="top" class="">
				<th><h3><?php _e( 'Uninstall:', 'geot' ); ?></h3></th>
				<td colspan="3">
					<p><?php _e( 'Check this if you want to <strong>delete all plugin data</strong> on uninstall' , 'geot' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'geot/settings_page/after' ); ?>
			<tr valign="top" class="">
				<th><label for=""><?php _e( 'Uninstall', 'geot' ); ?></label></th>
				<td colspan="3">
				        <input type="checkbox" id="" name="geot_settings[geot_uninstall]" value="1" <?php checked($opts['geot_uninstall'],'1');?>/>
						<p class="help"><?php _e( 'Will delete all database records and plugin settings when you delete the plugin', 'geot' ); ?></p>
				</td>
			</tr>

            <tr valign="top" class="">
				<th><h3><?php _e( 'Export/import:', 'geot' ); ?></h3></th>
				<td colspan="3">
					<p><?php _e( 'Export your setting or import them with a few clicks' , 'geot' ); ?></p>
				</td>
			</tr>
			<tr valign="top" class="">
				<th><label for=""><?php _e( 'Export settings', 'geot' ); ?></label></th>
				<td colspan="3">
                    <div id="export_href">

                    </div>
                    <script type="text/javascript">
                        var geot_settings = '<?php echo json_encode($opts);?>';
                        var data = "text/json;charset=utf-8," + encodeURIComponent(geot_settings);
                        jQuery('<a href="data:' + data + '" download="geot_settings.json" class="button">Export Settings</a>').appendTo('#export_href');
                    </script>
				</td>
			</tr>
			<tr valign="top" class="">
				<th><label for=""><?php _e( 'Import settings', 'geot' ); ?></label></th>
				<td colspan="3">
                        Select image to upload:
                        <input type="file" name="geot_settings_json" id="fileToUpload"><br />
                        <input type="submit" value="Import" name="submit">
				</td>
			</tr>

			<tr><td><input type="submit" class="button-primary" value="<?php _e( 'Save settings', 'geot' );?>"/></td>
			<?php wp_nonce_field('geot_save_settings','geot_nonce'); ?>
		</table>
	</form>
</div>
