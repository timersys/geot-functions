<div class="geot-setup-content">
	<form action="" method="">
		<?php do_action( 'geot/wizard_basic/before' ); ?>

		<p><?php _e('The wizard below will help you configure your Geotargeting plugin and start working quickly.','letsgo'); ?></p>

		<div class="location-row">
			<label for="license" class="location-label"><?php _e( 'Enter your API key', 'geot' ); ?></label>
			<input type="text" id="license" name="geot_settings[license]" value="" class="location-input api-keys"/>
			<div class="location-help"><?php _e( 'Enter your api key in order to connect with the API and also get automatic updates', 'geot' ); ?></div>
		</div>
		
		<div class="location-row">
			<label for="api_secret" class="location-label"><?php _e( 'Enter your API secret', 'geot' ); ?></label>
			<input type="password" id="api_secret" name="geot_settings[api_secret]" value="" class="location-input api-keys"/>
			<div class="location-help"><?php _e( 'Enter your api secret', 'geot' ); ?></div>
		</div>

		<?php do_action( 'geot/wizard_basic/after' ); ?>

		<div class="location-row">
			<button class="button-primary button button-hero button-next location-button" name="geot_settings[button]"><?php _e('Next','geot'); ?></button>
		</div>
	</form>
</div>