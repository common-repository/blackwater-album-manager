<?php

$balbum = get_query_var('balbum');
if (empty($balbum)) $balbum = '/';

$bam_album = @new blackwater_album($balbum);

function bam_up_path ($path) {
	if ('/' == substr($path, strlen($path)-1)) $path = substr_replace($path, '', strlen($path)-1);  // strip trailing slash
	if ('/' == substr($path, 0, 1)) $path = substr_replace($path, '', 0, 1); // strip leading slash
	$path = explode('/', $path);
	array_pop($path);
	$path = '/'.implode('/', $path).'/';
	if ('//' == $path) $path = '/';
	return $path;
}

function bam_albums_root () {
	global $wp_rewrite;
	if ($wp_rewrite->using_permalinks()) {
		if ($wp_rewrite->using_index_permalinks()) {
			return get_settings('home').'/index.php/albums';
		} else {
			return get_settings('home').'/albums';
		}
	} else {
		return get_settings('home').'?balbum=';
	}
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

<head profile="http://gmpg.org/xfn/11">
<title><?php echo get_bloginfo('wp_title') ?></title>
<?php echo '<link rel="stylesheet" href="'.get_settings('siteurl').'/wp-content/plugins/'.$bam_settings->plugin_folder().'/styles/display.css" type="text/css" media="screen" />'; ?>
</head>

<body>
<div id="content" class="widecolumn">
	<p><a href="<?php echo get_settings('siteurl') ?>">(&laquo;Back to Main Site)</a>
	<a href="<?php echo bam_albums_root().bam_up_path($bam_album->path) ?>">(Up a Folder)</a></p>
	
	<?php if (null !== $bam_album->path) : ?>
	
	<div class="bam-album">
	<div class="bam-album-name"><?php echo $bam_album->foldername() ?></div>

	<?php $bam_meta_names = bam_album_meta_names($bam_album->path); ?>
	<?php if (!empty($bam_meta_names)): ?>
	<div class="bam-album-meta">
		<table>
		<?php foreach ($bam_meta_names as $bam_meta_name) : ?>
		<?php $bam_meta_value = $bam_album->get_meta($bam_meta_name) ?>
			<tr>
			<th scope="row" class="bam-meta-name"><?php echo bam_picture_meta_displayname($bam_meta_name); ?></th>
			<td class="bam-meta-value"><?php if (!empty($bam_meta_value)) echo $bam_meta_value; else echo '-----------'; ?></td>
			</tr>
		<?php endforeach; ?>
		</table>
	</div>
	</div>
	<?php endif; ?>
	
	
	<?php if ($bam_album->have_album()) : ?>
		<div class="bam-child-albums">
		<?php while ($bam_this_album = $bam_album->next_album()) : ?>
			<?php echo '<div class="bam-child-album"><a href="'.bam_albums_root().$bam_this_album->path.'">'.$bam_this_album->foldername().'</a></div>'."\n"; ?>
		<?php endwhile; ?>
		</div>
	<?php endif; ?>
	
	
	<?php if ($bam_album->have_picture()) : ?>
	
		<div class="bam-picture-list">
		<h1>Pictures</h1>
		
		<?php while ($bam_picture = $bam_album->next_picture()) : ?>
		<?php $bam_meta_names = bam_picture_meta_names($bam_album->path); ?>
		
			<div class="bam-picture">
			<h2><?php echo $bam_picture->filename; ?></h2>
				<div class="bam-img">
				<a href="<?php echo $bam_settings->webpath.$bam_picture->album->path.$bam_picture->filename; ?>">
				<img src="<?php echo $bam_picture->thumbnail_src(600, 300); ?>" width="<?php echo $bam_picture->thumbnail_width(600, 300); ?>" height="<?php echo $bam_picture->thumbnail_height(600, 300); ?>" />
				</a>
				</div>

				<?php if (!empty($bam_meta_names)): ?>
				<div class="bam-picture-meta">
				<table>
				<?php foreach ($bam_meta_names as $bam_meta_name) : ?>
				<?php $bam_meta_value = $bam_picture->get_meta($bam_meta_name) ?>
					<tr>
					<th scope="row" class="bam-meta-name"><?php echo bam_picture_meta_displayname($bam_meta_name); ?></th>
					<td class="bam-meta-value"><?php if (!empty($bam_meta_value)) echo $bam_meta_value; else echo '-----------'; ?></td>
					</tr>
				<?php endforeach; ?>
				</table>
				</div>
				<?php endif; ?>

			</div>
		
		<?php endwhile; ?>
		
		</div>
		
		<?php endif; ?>
	
	
	<?php
	else :
	
	echo 'This album is not present.';
	
	endif;
	?>
	

</div>
</body>
</html>