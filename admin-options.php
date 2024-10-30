<?php

if (!current_user_can('blackwater_options')) {
	die('You do not have permission to access this panel.');
}

if (isset($_POST['action']) && 'update' == $_POST['action']) {
	update_option('blackwater_settings', stripslashes_deep(array($_POST['bam_gallery_webpath'], $_POST['bam_gallery_abspath'], $_POST['bam_allow_uploads'], $_POST['bam_allowed_exts'])));
	echo '<div class="updated"><p><strong>Options saved.</strong></p></div>';
	$bam_settings->get_settings();
}

?>

<div class="wrap">
	<h2>BlackWater Album Manager Options</h2>
	<form name="form1" method="post" action="<?php bloginfo('wpurl') ?>/wp-admin/options-general.php?page=<?php echo $bam_settings->plugin_folder(); ?>/admin-options.php">
		<input type="hidden" name="action" value="update" />
		
		<p>
		<fieldset class="options">
			<legend>Paths</legend>
			<table width="100%" cellspacing="2" cellpadding="5" class="editform">
				<tr valign="top">
					<th width="33%" scope="row">WebPath to Gallery:</th>
					<td><input name="bam_gallery_webpath" type="text" value="<?php echo $bam_settings->webpath; ?>" size="40" /><br />
					Recommended: <code><?php echo get_bloginfo('wpurl') . '/wp-content/blackwater'; ?></code><td>
				</tr>
				<tr valign="top">
					<th scope="row">Absolute Path to Gallery:</th>
					<td><input name="bam_gallery_abspath" type="text" value="<?php echo $bam_settings->abspath; ?>" size="40" /><br />
					Recommended: <code><?php echo ABSPATH . 'wp-content/blackwater'; ?></code></td>
				</tr>
			</table>
		</fieldset>
		</p>
		
		
		<p>
		<fieldset class="options">
			<legend>Web-Based File Uploads/Deletes</legend>
			<table width="100%" cellspacing="2" cellpadding="5" class="editform">
				<tr valign="top">
					<th width="33%" scope="row">Allowed?:</th>
					<td>
						<input type="radio" name="bam_allow_uploads" value="1" <?php if ($bam_settings->uploads_allow) echo 'checked="checked" '; ?>/> Yes&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						<input type="radio" name="bam_allow_uploads" value="0" <?php if (!$bam_settings->uploads_allow) echo 'checked="checked" '; ?>/> No
					</td>
				</tr>
				<tr valign="top">
					<th width="33%" scope="row">Allowed File Extensions:</th>
					<td>
						<input type="text" name="bam_allowed_exts" value="<?php echo $bam_settings->uploads_exts; ?>" /><br />
						Default: <code>jpg jpeg gif png</code>
					</td>
				</tr>
			</table>
		</fieldset>
		</p>
		
		
		<p class="submit">
			<input type="submit" name="Submit" value="Update Options &raquo;" />
		</p>
		
		
	</form>
</div>