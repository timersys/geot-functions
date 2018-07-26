<?php
/**
 * Settings page template
 * @since  1.0.0
 */


$opts     = geot_settings();
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
	'maxmind'                   => '0',
	'ip2location'               => '0',
	'geot_uninstall'            => '',
];
$opts     = wp_parse_args( $opts, apply_filters ('geot/default_settings', $defaults ) );

$countries 	= geot_countries();
?>
<script>
	var geot_countries = <?php echo json_encode(array_map(function($a){ return ['text' => $a->country,'value' => $a->iso_code];},(array)$countries));?>;
</script>
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
					<label><input type="text" id="license" name="geot_settings[license]" value="<?php  echo $opts['license'];?>" class="api-keys <?php echo 'geot_license_' ; echo !empty($opts['license']) && get_option( 'geot_license_active' ) ? get_option( 'geot_license_active' ) :'';?>" /><button class="button-primary check-license">Check Credits/Subscription</button>
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
			<?php if( file_exists(GeotFunctions\maxmind_db())):?>
				<tr valign="top" class="">
					<th><label for=""><?php _e( 'Enable Maxmind Database', 'geot'); ?></label></th>
					<td colspan="3">
						<label><input type="checkbox" id="" name="geot_settings[maxmind]" value="1" <?php checked($opts['maxmind'],'1');?>/>
							<p class="help"><?php _e( 'Check this if you want to use the database located on:', 'geot'); echo ' ' . GeotFunctions\maxmind_db();?></p>
					</td>
				</tr>
			<?php endif;?>

			<?php if( file_exists(GeotFunctions\ip2location_db())):?>
				<tr valign="top" class="">
					<th><label for=""><?php _e( 'Enable IP2Location Database', 'geot'); ?></label></th>
					<td colspan="3">
						<label><input type="checkbox" id="" name="geot_settings[ip2location]" value="1" <?php checked($opts['ip2location'],'1');?>/>
							<p class="help"><?php _e( 'Check this if you want to use the database located on:', 'geot'); echo ' ' . \GeotFunctions\ip2location_db();?></p>
					</td>
				</tr>
			<?php endif;?>

            <?php if( getenv( 'HTTP_GEOIP_COUNTRY_CODE' ) !== false ):?>
				<tr valign="top" class="">
					<th><label for=""><?php _e( 'Enable WPEngine Geolocation', 'geot'); ?></label></th>
					<td colspan="3">
						<label><input type="checkbox" id="" name="geot_settings[wpengine]" value="1" <?php checked($opts['wpengine'],'1');?>/>
							<p class="help"><?php _e( 'Check this if you want to use WPEngine database', 'geot');?></p>
					</td>
				</tr>
            <?php endif;?>

            <?php if( !empty( $_SERVER['HTTP_GEOIP_CITY_COUNTRY_NAME'] ) ):?>
				<tr valign="top" class="">
					<th><label for=""><?php _e( 'Enable Kinsta Geolocation', 'geot'); ?></label></th>
					<td colspan="3">
						<label><input type="checkbox" id="" name="geot_settings[kinsta]" value="1" <?php checked($opts['kinsta'],'1');?>/>
							<p class="help"><?php _e( 'Check this if you want to use Kinsta database', 'geot');?></p>
					</td>
				</tr>
            <?php endif;?>

			<tr valign="top" class="">
				<th><label for=""><?php _e( 'Cache Mode', 'geot'); ?></label></th>
				<td colspan="3">
					<label><input type="checkbox" id="" name="geot_settings[cache_mode]" value="1" <?php checked($opts['cache_mode'],'1');?>/>
						<p class="help"><?php echo sprintf(__( 'Check this if you want to save the user location into PHP Sessions. More info <a href="%s">here</a>', 'geot'), 'https://geotargetingwp.com/docs/geotargeting-pro/configuration#cache'); ?></p>
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
					foreach ( $opts['region'] as $region ) { $i++; ?>

						<div class="region-group"  data-id="<?php echo $i;?>" >

							<input type="text" class="region-name" placeholder="Enter region name" name="geot_settings[region][<?php echo $i;?>][name]" value="<?php echo !empty( $region['name'] )? esc_attr($region['name']): '' ; ?>"/>
							<a href="#" class="remove-region"title="<?php _e( 'Remove Region', 'geot' );?>">-</a>
							<select name="geot_settings[region][<?php echo $i;?>][countries][]" multiple class="geot-chosen-select-multiple" data-placeholder="<?php _e('Type country name...', 'geot' );?>" >
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
							<input type="text" class="region-name" placeholder="Enter region name" name="geot_settings[city_region][<?php echo $j;?>][name]" value="<?php echo !empty( $city_region['name'] )? esc_attr($city_region['name']): '' ; ?>"/>

							<a href="#" class="remove-city-region"title="<?php _e( 'Remove Region', 'geot' );?>">-</a>
							<select name="geot_settings[city_region][<?php echo $j;?>][countries][]"  class="country_ajax" data-counter="<?php echo $j;?>" data-placeholder="<?php _e('Type country name...', 'geot' );?>" >
								<option value=""><?php _e('Choose a Country', 'geot' );?></option>
								<?php
								foreach ($countries as $c) {
									?>
									<option value="<?php echo $c->iso_code?>" <?php isset( $city_region['countries'] ) && is_array( $city_region['countries'] ) ? selected(true, in_array( $c->iso_code, $city_region['countries']) ):''; ?>> <?php echo $c->country; ?></option>
									<?php
								}
								?>
							</select>

							<select name="geot_settings[city_region][<?php echo $j;?>][cities][]" multiple class="cities_container" id="<?php echo 'cities'.$j;?>" data-placeholder="<?php _e('First choose a country', 'geot' );?>" >
								<?php
								/*

								$country_cities = false;
								if( isset( $city_region['countries'] ) && is_array( $city_region['countries'] ) ) {
									$country_cities = reset($city_region['countries']);
								}
								$cities = json_decode(geot_get_cities($country_cities));
								if( $cities ) {
									foreach ($cities as $option_city) {
										echo '<option value="'.$option_city->city.'" ';
										echo isset($city_region['cities']) && is_array($city_region['cities']) ? selected(true, in_array($option_city->city, $city_region['cities'])) : '';
										echo '>'.$option_city->city.'</option>';
									}
								}*/
								?>
							</select>
							<script>
                               var select_<?= $j;?> = jQuery('#<?php echo 'cities'.$j;?>').selectize({
                                    plugins: ['remove_button'],
                                    valueField: 'name',
                                    labelField: 'name',
                                    searchField: 'name',
	                                options: <?php echo isset($city_region['cities']) && is_array($city_region['cities']) ? json_encode(array_map(function($a){ return ['name' => $a]; },$city_region['cities'])) : '""'; ?>,
	                                items: ['<?php echo isset($city_region['cities']) && is_array($city_region['cities']) ? implode("','",$city_region['cities']) :'';?>'],
                                    render: function (item,escape) {
                                        return '<div>' + escape(item.name) + '</div>';
                                    },
                                    preload: true,
                                    openOnFocus: true,
                                    onFocus: function () {
                                        var inst = select_<?= $j;?>[0].selectize;
                                        if( inst.loaded )
                                            return;
                                        inst.disable();
                                        jQuery.ajax({
                                            url: geot.ajax_url,
                                            type: 'POST',
                                            dataType: 'json',
                                            data: {
                                                action: 'geot_cities_by_country',
                                                country: '<?= reset($city_region['countries']);?>'
                                            },
                                            error: function () {

                                            },
                                            success: function (res) {
                                                inst.loaded = true;
                                                inst.enable();
                                                inst.addOption(res);
                                                inst.refreshOptions(true);
                                            }
                                        });
                                    }
                                });
							</script>
						</div>
						<hr>
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
