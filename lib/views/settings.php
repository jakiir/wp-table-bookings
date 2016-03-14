<?php
	global $wtbInit;
	$settings = get_option($wtbInit->options['settings']);	
?>

<div class="wrap">
	<div id="upf-icon-edit-pages" class="icon32 icon32-posts-page"><br /></div>
	<h2><?php _e('WTB Settings', WTB_SLUG); ?></h2>
	<form id="wtb-settings" onsubmit="wtbSettings(this); return false;">
		<h3><?php _e('General settings',WTB_SLUG);?></h3>
		<table class="form-table">
			<tr>
				<th scope="row"><label for="maxParty"><?php _e('Max Party limits',WTB_SLUG);?></label></th>
				<td>
				<input id="maxParty" name="general[party][max]" type="text" value="<?php echo (@$settings['general']['party']['max'] ? @$settings['general']['party']['max'] : 100); ?>" size="4" class="">
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="adminEmail"><?php _e('Admin E-mail',WTB_SLUG);?></label></th>
				<td>
				<input name="general[admin][mail]" type="text" value="<?php echo (@$settings['general']['admin']['mail'] ? @$settings['general']['admin']['mail'] : 'a@a.com'); ?>" size="30" class="">
				</td>
			</tr>
		</table>

		
		<p class="submit"><input type="submit" name="submit" id="wtbSaveButton" class="button button-primary" value="Save Changes"></p>

		<?php wp_nonce_field( $wtbInit->nonceText(), 'wtb_nonce' ); ?>
	</form>

	<div id="response" class="updated" style="display:none;"></div>
</div>
