<?php
/*
Plugin Name: BlackWater Album Manager
Plugin URI: http://dev.wp-plugins.org/browser/blackwater-album-manager/
Description: Picture gallery plugin for WordPress.
Version: 1.0 (pre-alpha)
Author: David Mabe
Author URI: http://www.ncpaddlers.com/
*/
/*  Copyright 2005  David Mabe  (email : canoeingkidd@users.sourceforge.net)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class blackwater_settings {
	var $abspath;
	var $webpath;
	var $uploads_allow;
	var $uploads_exts;
	var $permalink_struct;
	var $plugin_folder = null; // private
	var $gallery_base = null; // private
	var $gd_installed = null; // private
	
	function blackwater_settings () {
		$this->get_settings();
	}
	
	function get_settings () {
		if (!$options = get_option('blackwater_settings')) {
			add_action('init', array('blackwater_hooks', 'add_default_settings'));
		} else {
			$this->abspath = $options[1];
			$this->webpath = $options[0];
			$this->uploads_allow = $options[2];
			$this->uploads_exts = $options[3];
			$this->permalink_struct = $options[4];
		}
	}
	
	function plugin_folder () {
		if (null !== $this->plugin_folder) return $this->plugin_folder;
		$name = preg_replace('/^.*wp-content[\\\\\/]plugins[\\\\\/]/', '', dirname(__FILE__));
		return $this->plugin_folder = str_replace('\\', '/', $name);
	}
	
	function gallery_base () {
		if (null !== $this->gallery_base) return $this->gallery_base;
		if(substr($this->abspath, 0, strlen($_SERVER['DOCUMENT_ROOT'])) != $_SERVER['DOCUMENT_ROOT'])
			return $this->gallery_base = false;
		return $this->gallery_base = substr_replace($this->abspath, '', 0, strlen($_SERVER['DOCUMENT_ROOT']));
	}
	
	function gd_installed () {
		if (null !== $this->gd_installed) return $this->gd_installed;
		if (!function_exists('gd_info')) return $this->gd_installed = false;
		$GDArray = gd_info();
		$version = explode('.', ereg_replace('[[:alpha:][:space:]()]+', '', $GDArray['GD Version']));
		if ($version[0] < 2) return $this->gd_installed = false;
		if (($version[0] == 2) && ($version[1] == 0) && ($version[2] < 1)) return $this->gd_installed = false;
		return $this->gd_installed = true;
	}
}

$bam_settings = new blackwater_settings();

include 'picture.php';
include 'album.php';
include 'meta-manager.php';
// include 'buttonsnap.php';

class blackwater_hooks {
	function add_default_settings () {
		global $bam_settings, $wp_roles;
		
		$options = array (get_settings('siteurl').'/wp-content/blackwater', ABSPATH.'wp-content/blackwater', 1, 'jpg jpeg gif png', 'albums');
		
		update_option('blackwater_settings', $options);
		$bam_settings->get_settings();
		
		if (!file_exists($options[1])) mkdir($options[1]);
		
		$wp_roles->add_cap('administrator', 'blackwater_options');
		$wp_roles->add_cap('administrator', 'blackwater_manage');
		$wp_roles->add_cap('editor', 'blackwater_manage');
		$wp_roles->add_cap('author', 'blackwater_manage');
		
		bam_add_album_metafield('description', 'Description', 'textarea');
		bam_add_picture_metafield('description', 'Description', 'textarea');
	}
	function admin_pages () {
		global $bam_settings;
		add_management_page('BlackWater Management', 'BlackWater', 'blackwater_manage', dirname(__FILE__).'/admin-manage.php');
		add_options_page('BlackWater Options', 'BlackWater', 'blackwater_options', dirname(__FILE__).'/admin-options.php');
	}
	function buttonsnap () {
		buttonsnap_separator();
		$button_image_url = get_settings('siteurl') . '/wp-content/plugins/blackwater/images/button.gif';
		buttonsnap_jsbutton($button_image_url, 'Albums', 'window.open(\''.get_settings('siteurl').'/wp-content/plugins/blackwater/write-page.php?inalbum=/\', \'bamWritePost\', \'width=500,height=500,scrollbars=yes,resizable=yes\');');
	}
	function display_link () {
		global $bam_settings, $wp_rewrite;
		if ($wp_rewrite->using_permalinks()) {
			if ($wp_rewrite->using_index_permalinks()) {
				echo '<li><a href="'.get_settings('home').'/index.php/albums/">Albums</a></li>'."\n";
			} else {
				echo '<li><a href="'.get_settings('home').'/albums/">Albums</a></li>'."\n";
			}
		} else {
			echo '<li><a href="'.get_settings('home').'?balbum=/">Albums</a></li>'."\n";
		}
	}
	function wp_rewrite ($rewrite_rules) {
		global $wp_rewrite, $bam_settings;
		$token = '%balbum%';
		$wp_rewrite->add_rewrite_tag($token, $bam_settings->permalink_struct.'(.*)', 'balbum=');
		$structure = $wp_rewrite->root . $token;
		$rewrite = $wp_rewrite->generate_rewrite_rule($structure);
		return $rewrite + $rewrite_rules;
	}
	function add_query_var ($wpvar_array) {
		$wpvar_array[] = 'balbum';
		return $wpvar_array;
	}
	function display_redirect () {
		global $wp_rewrite, $bam_settings;
		if (get_query_var('balbum')) {
			include 'display.php';
			exit;
		}
		if ($wp_rewrite->using_permalinks()) {
			if ('/' == substr($_SERVER['REQUEST_URI'], strlen($_SERVER['REQUEST_URI'])-1)) {
				$request = substr_replace($_SERVER['REQUEST_URI'], '', strlen($_SERVER['REQUEST_URI'])-1);  // strip trailing slash
			} else {
				$request = $_SERVER['REQUEST_URI'];
			}
			$request = strrev($request);
			if (!strncasecmp($request, strrev($bam_settings->permalink_struct), strlen($bam_settings->permalink_struct))) {
				include 'display.php';
				exit;
			}
		}
	}
}

add_filter('rewrite_rules_array', array('blackwater_hooks', 'wp_rewrite'));
add_filter('query_vars', array('blackwater_hooks', 'add_query_var'));
add_action('template_redirect', array('blackwater_hooks', 'display_redirect'));
add_action('wp_meta', array('blackwater_hooks', 'display_link'));
add_action('admin_menu', array('blackwater_hooks', 'admin_pages'));
// add_action('admin_menu', array('blackwater_hooks', 'buttonsnap'));