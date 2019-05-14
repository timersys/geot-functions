<div class="geot-setup-content">
	<form action="" method="POST">
		<?php wp_nonce_field( 'geot-setup' ); ?>

		<?php do_action( 'geot/wizard/basic/before' ); ?>

		<p><?php _e('The wizard below will help you configure your Geotargeting plugin and start working quickly.','letsgo'); ?></p>

		<div class="location-row">
			<label for="license" class="location-label"><?php _e( 'Enter your API key', 'geot' ); ?></label>
			<input type="text" id="license" name="geot_settings[license]" value="<?php echo $opts['license']; ?>" class="location-input api-keys"/>
			<!--button class="button-secondary button button-hero button-next location-button-secondary"><?php //_e('Check Credits/Subscriptions','geot') ?></button-->
			<div class="location-help"><?php _e( 'Enter your api key in order to connect with the API and also get automatic updates', 'geot' ); ?></div>
		</div>
		
		<div class="location-row">
			<label for="api_secret" class="location-label"><?php _e( 'Enter your API secret', 'geot' ); ?></label>
			<input type="password" id="api_secret" name="geot_settings[api_secret]" value="<?php echo $opts['api_secret']; ?>" class="location-input api-keys"/>
			<div class="location-help"><?php _e( 'Enter your api secret', 'geot' ); ?></div>
		</div>

		<div class="location-row">
			<label for="region" class="location-label"><?php _e( 'Fallback Country', 'geot' ); ?></label>
			<select name="geot_settings[fallback_country]" class="geot-chosen-select" data-placeholder="<?php _e( 'Type country name...', 'geot' ); ?>">
				<option value=""><?php _e( 'Choose One', 'geot' ); ?></option>
				
				<?php foreach ( $countries as $c ) : ?>
					<option value="<?php echo $c->iso_code ?>" <?php isset( $opts['fallback_country'] ) ? selected( $c->iso_code, $opts['fallback_country'] ) : ''; ?>> <?php echo $c->country; ?></option>
				<?php endforeach; ?>
			</select>
			<div class="location-help"><?php _e( 'If the user IP is not detected, the plugin will fallback to this country', 'geot' ); ?></div>
		</div>


		<div class="location-row">
			<label for="bots" class="location-label"><?php _e( 'Bots Country', 'geot' ); ?></label>
			<select name="geot_settings[bots_country]" class="geot-chosen-select" data-placeholder="<?php _e( 'Type country name...', 'geot' ); ?>">
				<option value=""><?php _e( 'Choose One', 'geot' ); ?></option>
				
				<?php foreach ( $countries as $c ) : ?>
					<option value="<?php echo $c->iso_code ?>" <?php isset( $opts['bots_country'] ) ? selected( $c->iso_code, $opts['bots_country'] ) : ''; ?>> <?php echo $c->country; ?></option>
				<?php endforeach; ?>
			</select>
			<div class="location-help"><?php _e( 'All bots/crawlers will be treated as they were from this country. ', 'geot' ); ?></div>
		</div>

		<div class="location-row">
			<?php if( count($ips) == 0 ) : ?>
				<h2 class="text-center"><?php _e('There isnt IP', 'geot'); ?></h2>
			<?php elseif( count($ips) == 1 ) : ?>
				<h2 class="text-center"><?php printf(__('Your IP : %s', 'geot'), current($ips)); ?></h2>
			<?php else : ?>
				<label for="ip" class="location-label"><?php _e( 'IP', 'geot' ); ?></label>
				<select name="geot_settings[ips]" class="geot-chosen-select" data-placeholder="<?php _e( 'Choose your IP...', 'geot' ); ?>">
					<?php foreach( $ips as $ip ) : ?>
						<option value="<?php echo $ip; ?>"><?php echo $ip; ?></option>
					<?php endforeach; ?>
				</select>
				<div class="location-help"><?php printf(__('If you dont know what IP must choose, you can choose this IP %s','geot'), 'https://geotargetingwp.com/ip') ?></div>
			<?php endif; ?>
		</div>

		<?php do_action( 'geot/wizard/basic/after' ); ?>

		<div class="location-row text-center">
			<input type="hidden" name="save_step" value="1" />
			<button class="button-primary button button-hero button-next location-button" name="geot_settings[button]"><?php _e('Next','geot'); ?></button>
		</div>
	</form>
</div>