<?php

function bam_add_album_metafield ($name, $displayname, $fieldtype = 'text', $path = '/') {
	$ini = bam_read_from_metafields();
	
	$ini['AlbumDisplayNames'][$name] = $displayname;
	$ini['AlbumFieldTypes'][$name] = $fieldtype;
	$ini['AlbumPaths'][$name] = $path;
		
	bam_write_to_metafields($ini);
}

function bam_remove_album_metafield ($name) {
	$ini = bam_read_from_metafields();
	
	if (isset($ini['AlbumDisplayNames'][$name]))
		unset($ini['AlbumDisplayNames'][$name]);
		
	if (isset($ini['AlbumFieldTypes'][$name]))
		unset($ini['AlbumFieldTypes'][$name]);
		
	if (isset($ini['AlbumPaths'][$name]))
		unset($ini['AlbumPaths'][$name]);
	
	bam_write_to_metafields($ini);
}

function bam_add_picture_metafield ($name, $displayname, $fieldtype = 'text', $path = '/') {
	$ini = bam_read_from_metafields();
	
	$ini['PictureDisplayNames'][$name] = $displayname;
	$ini['PictureFieldTypes'][$name] = $fieldtype;
	$ini['PicturePaths'][$name] = $path;
	
	bam_write_to_metafields($ini);
}

function bam_remove_picture_metafield ($name) {
	$ini = bam_read_from_metafields();
	
	if (isset($ini['PictureDisplayNames'][$name]))
		unset($ini['PictureDisplayNames'][$name]);
		
	if (isset($ini['PictureFieldTypes'][$name]))
		unset($ini['PictureFieldTypes'][$name]);
		
	if (isset($ini['PicturePaths'][$name]))
		unset($ini['PicturePaths'][$name]);
	
	bam_write_to_metafields($ini);
}

function bam_clean_metafields () {
	global $bam_settings;
	
	$ini = bam_read_from_metafields();
	
	if (!isset($ini['AlbumPaths'])) return;
	if (!isset($ini['PicturePaths'])) return;
	
	foreach ($ini['AlbumPaths'] as $key => $value) {
		if (!file_exists($bam_settings->abspath.$value)) {
			unset($ini['AlbumPaths'][$key]);
			
			if (isset($ini['AlbumDisplayNames'][$key]))
				unset($ini['AlbumDisplayNames'][$key]);
			
			if (isset($ini['AlbumFieldTypes'][$key]))
				unset($ini['AlbumFieldTypes'][$key]);
			}
	}
	
	foreach ($ini['PicturePaths'] as $key => $value) {
		if (!file_exists($bam_settings->abspath.$value)) {
			unset($ini['PicturePaths'][$key]);
			
			if (isset($ini['PictureDisplayNames'][$key]))
				unset($ini['PictureDisplayNames'][$key]);
			
			if (isset($ini['PictureFieldTypes'][$key]))
				unset($ini['PictureFieldTypes'][$key]);
			}
	}
	
	$allalbums = array_merge($ini['AlbumPaths'], $ini['AlbumDisplayNames'], $ini['AlbumFieldTypes']);
	$allpictures = array_merge($ini['PicturePaths'], $ini['PictureDisplayNames'], $ini['PictureFieldTypes']);
	
	foreach ($allalbums as $key => $value) {
		if (!isset($ini['AlbumPaths'][$key]) || !isset($ini['AlbumDisplayNames'][$key]) || !isset($ini['AlbumFieldTypes'][$key])) {
			if (isset($ini['AlbumPaths'][$key]))
				unset($ini['AlbumPaths'][$key]);
			
			if (isset($ini['AlbumDisplayNames'][$key]))
				unset($ini['AlbumDisplayNames'][$key]);
			
			if (isset($ini['AlbumFieldTypes'][$key]))
				unset($ini['AlbumFieldTypes'][$key]);
		}
	}
	
	foreach ($allpictures as $key => $value) {
		if (!isset($ini['PicturePaths'][$key]) || !isset($ini['PictureDisplayNames'][$key]) || !isset($ini['PictureFieldTypes'][$key])) {
			if (isset($ini['PicturePaths'][$key]))
				unset($ini['PicturePaths'][$key]);
			
			if (isset($ini['PictureDisplayNames'][$key]))
				unset($ini['PictureDisplayNames'][$key]);
			
			if (isset($ini['PictureFieldTypes'][$key]))
				unset($ini['PictureFieldTypes'][$key]);
		}
	}
	
	bam_write_to_metafields($ini);
}

function bam_read_from_metafields () {
	global $bam_settings;
	
	if (file_exists($bam_settings->abspath.'/metafields.ini'))
		return $ini = parse_ini_file($bam_settings->abspath.'/metafields.ini', true);
	return array();
}

function bam_write_to_metafields ($assoc_array) {  // Credit: User notes at http://us3.php.net/parse_ini_file
	global $bam_settings;
	
	$path = $bam_settings->abspath.'/metafields.ini';
	
	$content = '';
	$sections = '';
	
	foreach ($assoc_array as $key => $item) {
		if (is_array($item)) {
			$sections .= "\n[{$key}]\n";
			foreach ($item as $key2 => $item2) {
				if (is_numeric($item2) || is_bool($item2))
					$sections .= "{$key2} = {$item2}\n";
				else
					$sections .= "{$key2} = \"{$item2}\"\n";
			}
		} else {
			if(is_numeric($item) || is_bool($item))
				$content .= "{$key} = {$item}\n";
			else
				$content .= "{$key} = \"{$item}\"\n";
		}
	}
	
	$content .= $sections;
	
	if (!$handle = fopen($path, 'w')) {
		return false;
	}
	
   if (!fwrite($handle, $content)) {
		return false;
	}
	
	fclose($handle);
	return true;
}

function bam_have_album_metafield ($name, $path = '/') {
	$ini = bam_read_from_metafields();
	
	if (isset($ini['AlbumPaths'][$name]) && (0 === strpos($path, $ini['AlbumPaths'][$name])))
		return true;
	
	return false;
}

function bam_have_picture_metafield ($name, $path = '/') {
	$ini = bam_read_from_metafields();
	
	if (isset($ini['PicturePaths'][$name]) && (0 === strpos($path, $ini['PicturePaths'][$name])))
		return true;
	
	return false;
}

function bam_album_meta_displayname ($name) {
	$ini = bam_read_from_metafields();
	
	if (isset($ini['AlbumDisplayNames'][$name]))
		return $ini['AlbumDisplayNames'][$name];
	
	return false;
}

function bam_picture_meta_displayname ($name) {
	$ini = bam_read_from_metafields();
	
	if (isset($ini['PictureDisplayNames'][$name]))
		return $ini['PictureDisplayNames'][$name];
	
	return false;
}

function bam_album_meta_names ($path = false) {
	$ini = bam_read_from_metafields();
	
	if (!isset($ini['AlbumPaths'])) return array();
	
	$names = array();
	
	if ($path) {
		foreach ($ini['AlbumPaths'] as $key => $value) {
			if (0 === strpos($path, $value)) {
				array_push($names, $key);
			}
		}
	} else {
		foreach ($ini['AlbumPaths'] as $key => $value) {
				array_push($names, $key);
		}
	}
	
	return $names;
}

function bam_picture_meta_names ($path = false) {
	$ini = bam_read_from_metafields();
	
	if (!isset($ini['PicturePaths'])) return array();
	
	$names = array();
	
	if ($path) {
		foreach ($ini['PicturePaths'] as $key => $value) {
			if (0 === strpos($path, $value)) {
				array_push($names, $key);
			}
		}
	} else {
		foreach ($ini['PicturePaths'] as $key => $value) {
			array_push($names, $key);
		}
	}
	
	return $names;
}

function bam_album_meta_fieldtype ($name) {
	$ini = bam_read_from_metafields();
	
	if (isset($ini['AlbumFieldTypes'][$name]))
		return $ini['AlbumFieldTypes'][$name];
	
	return false;
}

function bam_picture_meta_fieldtype ($name) {
	$ini = bam_read_from_metafields();
	
	if (isset($ini['PictureFieldTypes'][$name]))
		return $ini['PictureFieldTypes'][$name];
	
	return false;
}

function bam_album_meta_path ($name) {
	$ini = bam_read_from_metafields();
	
	if (isset($ini['AlbumPaths'][$name]))
		return $ini['AlbumPaths'][$name];
	
	return false;
}

function bam_picture_meta_path ($name) {
	$ini = bam_read_from_metafields();
	
	if (isset($ini['PicturePaths'][$name]))
		return $ini['PicturePaths'][$name];
	
	return false;
}