<?php

class blackwater_picture {
	var $album = null; // after initilization, only null if error occurred - public (should not be modified)
	var $filename = null; // after initilization, only null if error occurred - public (should not be modified)
	var $meta_values = null; // private
	var $imagesize = null; // private
	
	function blackwater_picture (&$album, $filename) {
		if ($this->check_path($album->path.$filename)) {
			$this->album = & $album;
			$this->filename = $filename;
			return true;
		} else {
			return false;
		}
	}
	
	function check_path ($path) {
		global $bam_settings;
		if (!file_exists($bam_settings->abspath.$path)) {
			trigger_error("$path picture not present", E_USER_WARNING);
			return false;
		}
		$realpath = realpath($bam_settings->abspath.$path);
		$realpath = str_replace('/', '\\', $realpath);
		$gallerybase = str_replace('/', '\\', $bam_settings->abspath);
		if (substr($realpath, 0, strlen($gallerybase)) != $gallerybase) {
			trigger_error("will not create pictures for files outsite of album root!", E_USER_WARNING);
			return false;
		}
		if (!$this->imagesize = getimagesize($bam_settings->abspath.$path)) {
			trigger_error("$path picture not present or image type not supported", E_USER_WARNING);
			return false;
		}
		return true;
	}
	
	function get_meta ($name = false) {
		if (null === $this->meta_values) {
			if ($ini = $this->album->get_ini()) {
				$this->meta_values = array();
				foreach ($ini as $key => $value) {
					if ('AlbumValues' != $key) {
						$this->meta_values[$key] = $value;
					}
				}
			} else {
				$this->meta_values = array();
			}
		}
		if (false === $name) {
			return $this->meta_values;
		} else {
			if (isset($this->meta_values[$this->filename][$name]))
				return $this->meta_values[$this->filename][$name];
			return '';
		}
	}
	
	function update_meta ($name, $value) {
		if (!bam_have_picture_metafield($name)) return false;
		$ini = $this->album->get_ini();
		$ini[$this->filename][$name] = $value;
		$this->album->meta_values = null;
		return $this->album->write_ini($ini);
	}
	
	function remove_meta ($name = false) {
		$ini = $this->album->get_ini();
		if (false === $name) unset($ini[$this->filename]);
		else unset($ini[$this->filename][$name]);
		$this->album->meta_values = null;
		return $this->album->write_ini($ini);
	}
	
	function get_width () {
		return $this->imagesize[0];
	}
	
	function get_height () {
		return $this->imagesize[1];
	}
	
	function thumbnail_src ($maxwidth, $maxheight) {
		global $bam_settings;
		if ($bam_settings->gd_installed() && (IMAGETYPE_JPEG == $this->imagesize[2])) {
			return get_settings('siteurl').'/wp-content/plugins/'.$bam_settings->plugin_folder().
				'/thumbnail.php?src='.$bam_settings->gallery_base().
				$this->album->path.$this->filename.'&amp;width='.
				$this->thumbnail_width($maxwidth, $maxheight).
				'&amp;height='.
				$this->thumbnail_height($maxwidth, $maxheight);
		}
		return $bam_settings->webpath.$this->album->path.$this->filename;
	}
	
	function thumbnail_width ($maxwidth, $maxheight) {
		$width = $this->get_width();
		$height = $this->get_height();
		if ($width <= $maxwidth && $height <= $maxheight) return $width;
		if (($width / $maxwidth) > ($height / $maxheight))
			$scale = $maxwidth / $width;
		else
			$scale = $maxheight / $height;
		
		return round($width * $scale);
	}
	
	function thumbnail_height ($maxwidth, $maxheight) {
		$width = $this->get_width();
		$height = $this->get_height();
		if ($width <= $maxwidth && $height <= $maxheight) return $height;
		if (($width / $maxwidth) > ($height / $maxheight))
			$scale = $maxwidth / $width;
		else
			$scale = $maxheight / $height;
		
		return round($height * $scale);
	}
	
	function delete () {
		global $bam_settings;
		return unlink($bam_settings->abspath.$this->album->path.$this->filename);
	}
}
