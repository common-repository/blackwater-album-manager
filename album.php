<?php

class blackwater_album {
	var $path = null; // after initilization, only null if error occurred - public (should not be modified)
	var $meta_values = null; // private
	var $folderlist = null; // private
	var $picturelist = null; // private
	
	function blackwater_album ($path, $create = false) {
		if ('/' == substr($path, strlen($path)-1)) $path = substr_replace($path, '', strlen($path)-1);  // strip trailing slash
		if ('/' == substr($path, 0, 1)) $path = substr_replace($path, '', 0, 1); // strip leading slash
		$path = '/'.$path.'/';
		if ('//' == $path) $path = '/';
		
		if ($this->check_dir($path, $create)) {
			$this->path = $path;
			return true;
		} else {
			return false;
		}
	}
	
	function check_dir ($path, $create) {
		global $bam_settings;
		if (!file_exists($bam_settings->abspath.$path)) {
			if ($create) {
				$this->path = $path;
				$this->create();
			} else {
				trigger_error("cannot create album - folder does not exist!", E_USER_WARNING);
				return false;
			}
		}
		$realpath = realpath($bam_settings->abspath.$path);
		$realpath = str_replace('/', '\\', $realpath);
		$gallerybase = str_replace('/', '\\', $bam_settings->abspath);
		if (substr($realpath, 0, strlen($gallerybase)) != $gallerybase) {
			$this->path = null;
			trigger_error("will not create album for folders outsite of album root!", E_USER_WARNING);
			return false;
		}
		if (!is_dir($bam_settings->abspath.$path)) {
			trigger_error("cannot create album - flat file exists with that name", E_USER_WARNING);
			return false;
		}
		return true;
	}
	
	function get_ini () {
		global $bam_settings;
		if (file_exists($bam_settings->abspath.$this->path.'album.ini'))
			return parse_ini_file($bam_settings->abspath.$this->path.'album.ini', true);
		return false;
	}
	
	function write_ini ($assoc_array) {  // Credit: User notes at http://us3.php.net/parse_ini_file
		global $bam_settings;
		$path = $bam_settings->abspath.$this->path.'album.ini';
		
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
	
	function foldername () { // returns the folder name of the album (NOT path)
		if ('/' == $this->path) {
			$foldername = $this->path;
		} else {
			$path = $this->path;
			if ('/' == substr($path, strlen($path)-1)) $path = substr_replace($path, '', strlen($path)-1);  // strip trailing slash
			if ('/' == substr($path, 0, 1)) $path = substr_replace($path, '', 0, 1); // strip leading slash
			$path = explode('/', $path);
			$foldername = array_pop($path);
		}
		return $foldername;
	}
	
	function get_meta ($name = false) {
		if (null === $this->meta_values) {
			if ($ini = $this->get_ini()) {
				$this->meta_values = $ini['AlbumValues'];
			} else {
				$this->meta_values = array();
			}
		}
		if (false === $name) {
			return $this->meta_values;
		} else {
			if (isset($this->meta_values[$name]))
				return $this->meta_values[$name];
			return '';
		}
	}
	
	function update_meta ($name, $value) {
		if (!bam_have_album_metafield($name)) return false;
		$ini = $this->get_ini();
		$ini['AlbumValues'][$name] = $value;
		$this->meta_values = null;
		return $this->write_ini($ini);
	}
	
	function create () {
		global $bam_settings;
		return mkdir($bam_settings->abspath.$this->path);
	}
	
	function delete () {
		global $bam_settings;
		$dir = opendir($bam_settings->abspath.$this->path);
		while (false !== $file = readdir($dir)) {
			if ('.' != $file && '..' != $file) {
				if (is_dir($bam_settings->abspath.$this->path.$file)) {
					$album = new blackwater_album($this->path.$file);
					$album->delete();
				} else {
					unlink($bam_settings->abspath.$this->path.$file);
				}
			}
		}
		closedir($dir);
		return rmdir($bam_settings->abspath.$this->path);
	}
	
	function &get_folderlist () {
		if (null !== $this->folderlist) return $this->folderlist;
		global $bam_settings;
		$dir = opendir($bam_settings->abspath.$this->path);
		$this->folderlist = array();
		while (false !== $file = readdir($dir)) {
			if (('.' != $file) && ('..' != $file) && (is_dir($bam_settings->abspath.$this->path.$file))) {
				array_push($this->folderlist, $file);
			}
		}
		closedir($dir);
		return $this->folderlist;
	}
	
	function reload_folderlist () {
		$folderlist = &$this->get_folderlist();
		$folderlist = null;
	}
	
	function have_album () {
		$folderlist = &$this->get_folderlist();
		return count($folderlist);
	}
	
	function next_album () {
		$folderlist = &$this->get_folderlist();
		if(null === $value = array_shift($folderlist)) return false;
		return new blackwater_album($this->path.$value);
	}
	
	function &get_picturelist () {
		if (null !== $this->picturelist) return $this->picturelist;
		global $bam_settings;
		$dir = opendir($bam_settings->abspath.$this->path);
		$this->picturelist = array();
		while (false !== $file = readdir($dir)) {
			if (('.' != $file) && ('..' != $file) && (!is_dir($bam_settings->abspath.$this->path.$file))) {
				if (exif_imagetype($bam_settings->abspath.$this->path.$file)) {
					array_push($this->picturelist, $file);
				}
			}
		}
		closedir($dir);
		return $this->picturelist;
	}
	
	function reload_picturelist () {
		$picturelist = &$this->get_picturelist();
		$picturelist = null;
	}
	
	function have_picture () {
		$picturelist = &$this->get_picturelist();
		return count($picturelist);
	}
	
	function next_picture () {
		$picturelist = &$this->get_picturelist();
		if (null === $value = array_shift($picturelist)) return false;
		return new blackwater_picture($this, $value);
	}
}