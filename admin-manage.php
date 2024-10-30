<?php

if (!current_user_can('blackwater_manage')) {
	die('You do not have permission to access this panel.');
}

if (isset($_GET['inalbum'])) {
	$bam_inalbum = stripslashes($_GET['inalbum']);
} else {
	$bam_inalbum = '/';
}

function bam_sanitize_ini_key ($key) {
	$replace = array (
		'=' => '-',
		'"' => '\''
	);
	return strtr(stripslashes($key), $replace);
}

function bam_sanitize_ini_value ($value) {
	return strtr(stripslashes($value), '"', '\'');
}

$bam_album = new blackwater_album($bam_inalbum);


function bam_process_post () {
	global $bam_settings, $bam_album;
	if (isset($_GET['deletealbum'])) {
		
		if ($bam_settings->uploads_allow) {
			$album = new blackwater_album(stripslashes($_GET['deletealbum']));
			$album->delete();
			$bam_album->reload_folderlist();
		}
		
	} elseif (isset($_POST['addalbum'])) {
		
		new blackwater_album($bam_album->path.stripslashes($_POST['album_foldername']), true);
		$bam_album->reload_folderlist();
		
	} elseif (isset($_POST['editalbummeta'])) {
		
		$album = new blackwater_album($bam_album->path);
		$album_meta = bam_album_meta_names($bam_album->path);
		foreach ($album_meta as $meta_name) {
			$album->update_meta(stripslashes($meta_name), bam_sanitize_ini_value($_POST[$meta_name]));
		}
		
	} elseif (isset($_POST['addpicture'])) {
		
		if ($bam_settings->uploads_allow) {
			$allow_exts = explode(' ', $bam_settings->uploads_exts);
			$uploaded_ext = pathinfo($_FILES['picture']['name']);
			$uploaded_ext = $uploaded_ext['extension'];
			$allowed = false;
			foreach ($allow_exts as $allow_ext) {
				if (!strcasecmp($uploaded_ext, $allow_ext)) {
					$allowed = true;
					break;
				}
			}
			if ($allowed) {
				move_uploaded_file($_FILES['picture']['tmp_name'], $bam_settings->abspath.$bam_album->path.$_FILES['picture']['name']);
			} else {
				trigger_error("files of type $uploaded_ext are not allowed", E_USER_WARNING);
			}
			$bam_album->reload_picturelist();
		}
		
	} elseif (isset($_GET['deletepicture'])) {
		
		if ($bam_settings->uploads_allow) {
			$picture = new blackwater_picture($bam_album, stripslashes($_GET['deletepicture']));
			$picture->delete();
			$bam_album->reload_picturelist();
		}
		
	} elseif (isset($_POST['editpicsmeta'])) {
		
		$picture_metas = bam_picture_meta_names($bam_album->path);
		foreach ($picture_metas as $meta_name) {
			while ($picture = $bam_album->next_picture()) {
				foreach ($_POST[$meta_name] as $meta_file => $meta_value) {
					if ($picture->filename == stripslashes($meta_file)) {
						$picture->update_meta(stripslashes($meta_name), bam_sanitize_ini_value($meta_value));
					}
				}
			}
		}
		$bam_album->reload_picturelist();
		
	} elseif (isset($_GET['deletealbummetafield'])) {
		
		bam_remove_album_metafield(stripslashes($_GET['deletealbummetafield']));
		
	} elseif (isset($_GET['deletepicturemetafield'])) {
		
		bam_remove_picture_metafield(stripslashes($_GET['deletepicturemetafield']));
		
	} elseif (isset($_POST['addalbummetafield'])) {
		
		bam_add_album_metafield(bam_sanitize_ini_key($_POST['name']), bam_sanitize_ini_value($_POST['displayname']), bam_sanitize_ini_value($_POST['fieldtype']), bam_sanitize_ini_value($_POST['tree']));
		
	} elseif (isset($_POST['addpicturemetafield'])) {
		
		bam_add_picture_metafield(bam_sanitize_ini_key($_POST['name']), bam_sanitize_ini_value($_POST['displayname']), bam_sanitize_ini_value($_POST['fieldtype']), bam_sanitize_ini_value($_POST['tree']));
		
	}
}

function bam_up_path () {
	global $bam_album;
	$path = $bam_album->path;
	if ('/' == substr($path, strlen($path)-1)) $path = substr_replace($path, '', strlen($path)-1);  // strip trailing slash
	if ('/' == substr($path, 0, 1)) $path = substr_replace($path, '', 0, 1); // strip leading slash
	$path = explode('/', $path);
	array_pop($path);
	$path = '/'.implode('/', $path).'/';
	if ('//' == $path) $path = '/';
	return $path;
}

bam_process_post();

?>


<div class="wrap">
	<a href="<?php echo get_settings('siteurl') ?>/wp-admin/edit.php?page=<?php echo $bam_settings->plugin_folder(); ?>/admin-manage.php">Albums</a> | <a href="<?php echo get_settings('siteurl') ?>/wp-admin/edit.php?page=<?php echo $bam_settings->plugin_folder(); ?>/admin-manage.php&amp;section=metafields">Metafields</a>
</div>


<?php if (isset($_GET['section']) && 'metafields' == $_GET['section']) : ?>


<div class="wrap">
	<form name="editalbummetafields" method="post" action="<?php echo get_settings('siteurl') ?>/wp-admin/edit.php?page=<?php echo $bam_settings->plugin_folder(); ?>/admin-manage.php&amp;section=metafields">
	<h2>Album Metafields</h2>
	<table width="100%" cellspacing="3" cellpadding="3">
		<th>Name</th>
		<th>Display Name</th>
		<th>Affecting Tree</th>
		<th>Field Type</th>
		<th>&nbsp;</th>
		<?php $bam_meta_names = bam_album_meta_names(); ?>
		<?php $bam_odd = true ?>
		<?php foreach ($bam_meta_names as $bam_meta_name) : ?>
			<tr<?php if ($bam_odd) echo ' bgcolor="#eeeeee"'; ?>>
				<td><?php echo $bam_meta_name ?></td>
				<td><?php echo bam_album_meta_displayname($bam_meta_name) ?></td>
				<td><?php echo bam_album_meta_path($bam_meta_name) ?></td>
				<td><?php echo bam_album_meta_fieldtype($bam_meta_name) ?></td>
				<td>
					<a class="delete" href="<?php bloginfo('wpurl'); ?>/wp-admin/edit.php?page=<?php echo $bam_settings->plugin_folder(); ?>/admin-manage.php&amp;deletealbummetafield=<?php echo $bam_meta_name ?>&amp;section=metafields" onclick="return confirm('You are about to delete this album metafield &quot;<?php echo str_replace("'", '&#146;', $bam_meta_name); ?>&quot;\n  &quot;OK&quot; to delete, &quot;Cancel&quot; to stop.')">Delete</a>
				</td>
			</tr>
		<?php endforeach ?>
	</table>
	</form>
</div>


<div class="wrap">
	<form name="editpicturemetafields" method="post" action="<?php echo get_settings('siteurl') ?>/wp-admin/edit.php?page=<?php echo $bam_settings->plugin_folder(); ?>/admin-manage.php&amp;section=metafields">
	<h2>Picture Metafields</h2>
	<table width="100%" cellspacing="3" cellpadding="3">
		<th>Name</th>
		<th>Display Name</th>
		<th>Affecting Tree</th>
		<th>Field Type</th>
		<th>&nbsp;</th>
		<?php $bam_meta_names = bam_picture_meta_names(); ?>
		<?php $bam_odd = true ?>
		<?php foreach ($bam_meta_names as $bam_meta_name) : ?>
			<tr<?php if ($bam_odd) echo ' bgcolor="#eeeeee"'; ?>>
				<td><?php echo $bam_meta_name ?></td>
				<td><?php echo bam_picture_meta_displayname($bam_meta_name) ?></td>
				<td><?php echo bam_picture_meta_path($bam_meta_name) ?></td>
				<td><?php echo bam_picture_meta_fieldtype($bam_meta_name) ?></td>
				<td>
					<a class="delete" href="<?php bloginfo('wpurl'); ?>/wp-admin/edit.php?page=<?php echo $bam_settings->plugin_folder(); ?>/admin-manage.php&amp;deletepicturemetafield=<?php echo $bam_meta_name ?>&amp;section=metafields" onclick="return confirm('You are about to delete this picture metafield &quot;<?php echo str_replace("'", '&#146;', $bam_meta_name); ?>&quot;\n  &quot;OK&quot; to delete, &quot;Cancel&quot; to stop.')">Delete</a>
				</td>
			</tr>
		<?php endforeach ?>
	</table>
	</form>
</div>


<div class="wrap" align="left">
	<form name="addalbummetafield" method="post" action="<?php echo get_settings('siteurl') ?>/wp-admin/edit.php?page=<?php echo $bam_settings->plugin_folder(); ?>/admin-manage.php&amp;section=metafields">
	<h2>Add Album Metafield</h2>
	<table cellspacing="3" cellpadding="3">
		<tr>
		<th scope="row">Name</th>
		<td><input type="textbox" width="100" name="name" /> <strong>Warning:</strong> album fields with same name will be overwritten.</td>
		</tr>
		
		<tr>
		<th scope="row">Display Name</th>
		<td><input type="textbox" width="100" name="displayname" /></td>
		</tr>
		
		<tr>
		<th scope="row">Affecting Tree</th>
		<td><input type="textbox" width="100" name="tree" /></td>
		</tr>
		
		<tr>
		<th scope="row">Field Type</th>
		<td>
		<select name="fieldtype">
			<option value="textbox">Textbox</option>
			<option value="textarea">Textarea</option>
		</select>
		</td>
		</tr>
		
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="addalbummetafield" value="Submit" /></td>
		</tr>
		
	</table>
	</form>
</div>


<div class="wrap" align="left">
	<form name="addpicturemetafield" method="post" action="<?php echo get_settings('siteurl') ?>/wp-admin/edit.php?page=<?php echo $bam_settings->plugin_folder(); ?>/admin-manage.php&amp;section=metafields">
	<h2>Add Picture Metafield</h2>
	<table cellspacing="3" cellpadding="3">
		<tr>
		<th scope="row">Name</th>
		<td><input type="textbox" width="100" name="name" /> <strong>Warning:</strong> picture fields with same name will be overwritten.</td>
		</tr>
		
		<tr>
		<th scope="row">Display Name</th>
		<td><input type="textbox" width="100" name="displayname" /></td>
		</tr>
		
		<tr>
		<th scope="row">Affecting Tree</th>
		<td><input type="textbox" width="100" name="tree" /></td>
		</tr>
		
		<tr>
		<th scope="row">Field Type</th>
		<td>
		<select name="fieldtype">
			<option value="textbox">Textbox</option>
			<option value="textarea">Textarea</option>
		</select>
		</td>
		</tr>
		
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="addpicturemetafield" value="Submit" /></td>
		</tr>
		
	</table>
	</form>
</div>


<?php else : ?>


<div class="wrap">
	<?php if('/' != $bam_album->path) : ?>
		<strong>(<a href="<?php echo get_settings('siteurl') ?>/wp-admin/edit.php?page=<?php echo $bam_settings->plugin_folder(); ?>/admin-manage.php&amp;inalbum=<?php echo bam_up_path() ?>" />up</a>)</strong>
	<?php endif ?>
	<?php echo $bam_album->path ?>
</div>


<?php if ($bam_album->have_album()) : ?>
	<div class="wrap">
		<h2>Child Albums</h2>
		<table width="100%" cellspacing="3" cellpadding="3">
			<?php $bam_odd = true; ?>
			<?php while ($bam_this_album = $bam_album->next_album()) : ?>
				<tr>
					<th scope="row" align="left"<?php if ($bam_odd) echo ' bgcolor="#eeeeee"'; ?>>
						<?php echo $bam_this_album->foldername(); ?>
					</th>
					<td<?php if ($bam_odd) echo ' bgcolor="#eeeeee"'; ?>>
						<a class="edit" href="<?php bloginfo('wpurl'); ?>/wp-admin/edit.php?page=<?php echo $bam_settings->plugin_folder(); ?>/admin-manage.php&amp;inalbum=<?php echo $bam_this_album->path; ?>">Open</a>
					</td>
					<?php if ($bam_settings->uploads_allow) : ?>
						<td<?php if ($bam_odd) echo ' bgcolor="#eeeeee"'; ?>>
							<a class="delete" href="<?php bloginfo('wpurl'); ?>/wp-admin/edit.php?page=<?php echo $bam_settings->plugin_folder(); ?>/admin-manage.php&amp;deletealbum=<?php echo $bam_this_album->path; ?>&amp;inalbum=<?php echo $bam_album->path; ?>" onclick="return confirm('You are about to delete this album &quot;<?php echo $bam_this_album->foldername(); ?>&quot; (including all files and folders in it)\n  \'OK\' to delete, \'Cancel\' to stop.')">Delete</a>
						</td>
					<?php endif ?>
				</tr>
				<?php $bam_odd = !$bam_odd; ?>
			<?php endwhile; ?>
		</table>
	</div>
<?php endif; ?>


<div class="wrap">
	<h2>Add Child Album</h2>
	<form name="addalbum" method="post" action="<?php echo get_settings('siteurl') ?>/wp-admin/edit.php?page=<?php echo $bam_settings->plugin_folder(); ?>/admin-manage.php&amp;inalbum=<?php echo $bam_album->path ?>">
		<table width="100%">
			<tr>
				<th scope="row">Folder Name</th>
				<td><input name="album_foldername" type="text" size="30" /></td>
			<tr>
			<tr>
				<td>&nbsp;</td>
				<td><input type="submit" name="addalbum" value="Add Album" /></td>
			</tr>
		</table>
	</form>
</div>


<div class="wrap">
	<h2>Edit Album Meta</h2>
	<form name="editalbummeta" method="post" action="<?php echo get_settings('siteurl') ?>/wp-admin/edit.php?page=<?php echo $bam_settings->plugin_folder(); ?>/admin-manage.php&amp;inalbum=<?php echo $bam_album->path ?>">
		<table width="100%">
			<?php
			$bam_album_meta = bam_album_meta_names($bam_inalbum);
			foreach ($bam_album_meta as $bam_meta_name) { ?>
			<tr>
				<th scope="row"><?php echo bam_album_meta_displayname($bam_meta_name); ?></th>
			<?php if ('text' == $bam_fieldtype = bam_album_meta_fieldtype($bam_meta_name)) { ?>
				<td><input name="<?php echo $bam_meta_name; ?>" type="text" size="30" value="<?php echo $bam_album->get_meta($bam_meta_name); ?>" /></td>
			<?php } elseif ('textarea' == $bam_fieldtype) { ?>
				<td><textarea name="<?php echo $bam_meta_name; ?>" rows="2" cols="40"><?php echo $bam_album->get_meta($bam_meta_name); ?></textarea></td>
			<?php } ?>
			<tr>
			<?php } ?>
			<tr>
				<td>&nbsp;</td>
				<td><input type="submit" name="editalbummeta" value="Update Album Meta" /></td>
			</tr>
		</table>
	</form>
</div>


<?php if ($bam_album->have_picture()) : ?>
<div class="wrap">
	<h2>Pictures</h2>
	<form name="editpicsmeta" method="post" action="<?php bloginfo('wpurl') ?>/wp-admin/edit.php?page=<?php echo $bam_settings->plugin_folder(); ?>/admin-manage.php&amp;inalbum=<?php echo $bam_album->path ?>">
		<?php
			$bam_meta_names = bam_picture_meta_names($bam_inalbum);
			while($bam_this_picture = $bam_album->next_picture()) :
				?>
				<div class="bam-picture">
					<div class="bam-filename">
					<strong>
					<?php echo $bam_this_picture->filename ?></strong><?php if ($bam_settings->uploads_allow) : ?>
						 (<a href="<?php echo get_settings('siteurl'); ?>/wp-admin/edit.php?page=<?php echo $bam_settings->plugin_folder(); ?>/admin-manage.php&amp;deletepicture=<?php echo $bam_this_picture->filename ?>&amp;inalbum=<?php echo $bam_album->path; ?>" onclick="return confirm('You are about to delete this picture &quot;<?php echo $bam_album->filename ?>&quot;\n  \'OK\' to delete, \'Cancel\' to stop.')">delete</a>)<?php endif; ?>
					</div>
					
					<div class="bam-thumb">
					<a href="<?php echo $bam_settings->webpath.$bam_album->path.$bam_this_picture->filename ?>"><img src="<?php echo $bam_this_picture->thumbnail_src(500, 500) ?>" width="<?php echo $bam_this_picture->thumbnail_width(500, 500) ?>" height="<?php echo $bam_this_picture->thumbnail_height(500, 500) ?>" /></a>
					</div>
					
				<table class="bam-meta">
				<?php foreach ($bam_meta_names as $bam_meta_name) : ?>
					<tr>
					<td>
					<strong><?php echo bam_picture_meta_displayname($bam_meta_name); ?></strong>
					</td>
					<td>
					<?php if ('text' == $bam_fieldtype = bam_picture_meta_fieldtype($bam_meta_name)) { ?>
					<input type="text" size="30" name="<?php echo $bam_meta_name ?>[<?php echo $bam_this_picture->filename ?>]" value="<?php echo $bam_this_picture->get_meta($bam_meta_name) ?>" />
					<?php } elseif ('textarea' == $bam_fieldtype) { ?>
					<textarea name="<?php echo $bam_meta_name ?>[<?php echo $bam_this_picture->filename ?>]" rows="2" cols="40"><?php echo $bam_this_picture->get_meta($bam_meta_name) ?></textarea>
					<?php } ?>
					</td>
					</tr>
				<?php endforeach; ?>
				</table>
				</div>
				<br />
			<?php endwhile ?>
		<input type="submit" name="editpicsmeta" value="Update Picture Meta" />
	</form>
</div>
<?php endif; ?>


<?php if ($bam_settings->uploads_allow) : ?>
	<div class="wrap">
		<form name="addpicture" enctype="multipart/form-data" method="post" action="<?php echo get_settings('siteurl') ?>/wp-admin/edit.php?page=<?php echo $bam_settings->plugin_folder() ?>/admin-manage.php&amp;inalbum=<?php echo $bam_album->path ?>">
			<h2>Add Picture</h2>
			Allowed Extensions: <code><?php echo $bam_settings->uploads_exts; ?></code><br />
			<input name="picture" type="file" size="30" /><br />
			<input type="submit" name="addpicture" value="Add Picture" />
		</form>
	</div>
<?php endif; ?>


<?php endif ?>