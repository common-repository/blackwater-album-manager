<?php
/*******************************************************************************
BUTTONSNAP CLASS LIBRARY By Owen Winkler
http://asymptomatic.net
WordPress Downloads are at http://redalt.com/downloads
Version: 1.1
*******************************************************************************/

if (!class_exists('buttonsnap')) :
class buttonsnap
{
	var $script_output = false;
	var $buttons = array();
	
	function sink_hooks()
	{
		add_action('edit_form_advanced', array(&$this, 'edit_form_advanced'));
		add_action('edit_page_form', array(&$this, 'edit_form_advanced'));
	}
	
	function go_solo()
	{
		$dispatch = isset($_POST['buttonsnapdispatch']) ? $_POST['buttonsnapdispatch'] : @$_GET['buttonsnapdispatch'];
		if($dispatch != '') {
			auth_redirect();
			$selection = isset($_POST['selection']) ? $_POST['selection'] : @$_GET['selection'];
			$selection = apply_filters($dispatch, $selection);
			die($selection);
		}
	}
	
	function edit_form_advanced()
	{
		if (!$this->script_output) {
			$this->output_script();
			$this->script_output = true;
		}
	}
	
	function output_script()
	{
		echo '<script type="text/javascript">
		var buttonsnap_request_uri = "' . $this->plugin_uri() . '";
		var buttonsnap_wproot = "' . get_settings('siteurl') . '";
		</script>' . "\n";
echo <<< ENDSCRIPT

<script type="text/javascript">
addLoadEvent(function () { window.setTimeout('buttonsnap_addbuttons()',1000); });
var buttonsnap_mozilla = document.getElementById&&!document.all;
function buttonsnap_safeclick(e)
{
	if(!buttonsnap_mozilla) {
		e.returnValue = false;
		e.cancelBubble = true;
	}
}
function buttonsnap_addEvent( obj, type, fn )
{
	if (obj.addEventListener)
		obj.addEventListener( type, fn, false );
	else if (obj.attachEvent)
	{
		obj["e"+type+fn] = fn;
		obj[type+fn] = function() { obj["e"+type+fn]( window.event ); }
		obj.attachEvent( "on"+type, obj[type+fn] );
	}
}
function buttonsnap_newbutton(src, alt) {
	if(window.tinyMCE) {
		var anchor = document.createElement('A');
		anchor.setAttribute('href', 'javascript:;');
		anchor.setAttribute('title', alt);
		var newimage = document.createElement('IMG');
		newimage.setAttribute('src', src);
		newimage.setAttribute('alt', alt);
		newimage.setAttribute('class', 'mceButtonNormal');
		buttonsnap_addEvent(newimage, 'mouseover', function() {tinyMCE.switchClass(this,'mceButtonOver');});
		buttonsnap_addEvent(newimage, 'mouseout', function() {tinyMCE.switchClass(this,'mceButtonNormal');}); //restoreClass(this)
		buttonsnap_addEvent(newimage, 'mousedown', function() {tinyMCE.restoreAndSwitchClass(this,'mceButtonDown');});
		anchor.appendChild(newimage);
		brs = mcetoolbar.getElementsByTagName('BR');
		if(brs.length > 0)
			mcetoolbar.insertBefore(anchor, brs[0]);
		else
			mcetoolbar.appendChild(anchor);
	}
	else if(window.qttoolbar)
	{
		var anchor = document.createElement('input');
		anchor.type = 'button';
		anchor.value = alt;
		anchor.className = 'ed_button';
		anchor.title = alt;
		anchor.id = 'ed_' + alt;
		qttoolbar.appendChild(anchor);
	}	
	return anchor;
}
function buttonsnap_newseparator() {
	if(window.tinyMCE) {
		var sep = document.createElement('IMG');
		
		sep.setAttribute('src', buttonsnap_wproot + '/wp-includes/js/tinymce/themes/advanced/images/spacer.gif');
		sep.className = 'mceSeparatorLine';
		sep.setAttribute('class', 'mceSeparatorLine');
		sep.setAttribute('height', '16');
		sep.setAttribute('width', '1');
		brs = mcetoolbar.getElementsByTagName('BR');
		if(brs.length > 0)
			mcetoolbar.insertBefore(sep, brs[0]);
		else
			mcetoolbar.appendChild(sep);
	}
}
function buttonsnap_settext(text) {
	if(window.tinyMCE) {
		window.tinyMCE.execInstanceCommand('content', 'mceInsertContent', false, text);
	} else {
		edInsertContent(edCanvas, text);
	}
}
function buttonsnap_ajax(dispatch) {
	if(window.tinyMCE) {
		selection = tinyMCE.getInstanceById('content').getSelectedText();
	}
	else {	
		if (document.selection) {
			document.getElementById('content').focus();
		  sel = document.selection.createRange();
			if (sel.text.length > 0) {
				selection = sel.text;
			}
			else {
				selection = '';
			}
		}
		else {
			selection = '';
		}
	}

	var ajax = new sack(buttonsnap_request_uri);
	ajax.setVar('buttonsnapdispatch', dispatch);
	ajax.setVar('selection', selection);
	ajax.onCompletion = function () {buttonsnap_settext(this.response);};
	ajax.runAJAX();
}
var mcetoolbar;
var qttoolbar = document.getElementById("ed_toolbar");
function buttonsnap_addbuttons () {
	if(window.tinyMCE) {
		try {
			var edit = document.getElementById(window.tinyMCE.getEditorId('content'));
			for(table = edit;table.tagName != 'TABLE';table = table.parentNode);
			mcetoolbar = table.rows[0].firstChild;
		}
		catch(e) {
			setTimeout('buttonsnap_addbuttons()', 5000);
			return;
		}
	}
	try {
ENDSCRIPT;
		
		foreach ($this->buttons as $button) {
			if($button['type'] == 'separator') {
				echo "buttonsnap_newseparator();\n";
			}
			else {
				echo "newbtn = buttonsnap_newbutton('{$button['src']}', '{$button['alt']}');\n";
				switch($button['type']) {
				case 'text':
					echo "buttonsnap_addEvent(newbtn, 'click', function(e) {buttonsnap_settext(\"{$button['text']}\");buttonsnap_safeclick(e);});\n";
					break;
				case 'js':
					echo "buttonsnap_addEvent(newbtn, 'click', function(e) {" . $button['js'] . "buttonsnap_safeclick(e);});\n";
					break;
				case 'ajax':
					echo "buttonsnap_addEvent(newbtn, 'click', function(e) {buttonsnap_ajax(\"{$button['hook']}\");buttonsnap_safeclick(e);});\n";
					break;
				default:
					echo "buttonsnap_addEvent(newbtn, 'click', function(e) {alert(\"The :{$button->type}: button is an invalid type\");buttonsnap_safeclick(e);});\n";
				}
			}
		}
echo <<< MORESCRIPT
	}
	catch(e) {
		setTimeout('buttonsnap_addbuttons()', 5000);
	}
}
</script>

MORESCRIPT;
	}
	
	function textbutton($imgsrc, $alttext, $inserted)
	{
		$this->buttons[] = array('type'=>'text', 'src'=>$imgsrc, 'alt'=>$alttext, 'text'=>$inserted);
		return $this->buttons;
	}
	
	function jsbutton($imgsrc, $alttext, $js)
	{
		$this->buttons[] = array('type'=>'js', 'src'=>$imgsrc, 'alt'=>$alttext, 'js'=>$js);
		return $this->buttons;
	}

	function ajaxbutton($imgsrc, $alttext, $hook)
	{
		$this->buttons[] = array('type'=>'ajax', 'src'=>$imgsrc, 'alt'=>$alttext, 'hook'=>$hook);
		return $this->buttons;
	}
	
	function separator()
	{
		$this->buttons[] = array('type'=>'separator');
		return $this->buttons;
	}
	
	function basename() 
	{
		$name = preg_replace('/^.*wp-content[\\\\\/]plugins[\\\\\/]/', '', __FILE__);
		return str_replace('\\', '/', $name);
	}
	
	function plugin_uri()
	{
		return get_settings('siteurl') . '/wp-content/plugins/' . $this->basename(); 
	}
	
	function include_up($filename) {
		$c=0;
		while(!is_file($filename)) {
			$filename = '../' . $filename;
			$c++;
			if($c==30) {
				echo 'Could not find ' . basename($filename) . '.'; return '';
			}
		}
		return $filename;
	}

	function debug($foo)
	{
		$args = func_get_args();
		echo "<pre style=\"background-color:#ffeeee;border:1px solid red;\">";
		foreach($args as $arg1)
		{
			echo htmlentities(print_r($arg1, 1)) . "<br/>";
		}
		echo "</pre>";
	}
}
$buttonsnap = new buttonsnap();
function buttonsnap_textbutton($imgsrc, $alttext, $inserted) { global $buttonsnap; return $buttonsnap->textbutton($imgsrc, $alttext, $inserted);}
function buttonsnap_jsbutton($imgsrc, $alttext, $js) { global $buttonsnap; return $buttonsnap->jsbutton($imgsrc, $alttext, $js);}
function buttonsnap_ajaxbutton($imgsrc, $alttext, $hook) { global $buttonsnap; return $buttonsnap->ajaxbutton($imgsrc, $alttext, $hook);}
function buttonsnap_separator() { global $buttonsnap; return $buttonsnap->separator();}
function buttonsnap_dirname() {global $buttonsnap; return dirname($buttonsnap->plugin_uri());}
endif;
if (!defined('ABSPATH')) {
  require_once($buttonsnap->include_up('wp-config.php'));
  $buttonsnap->go_solo();
}
else {
	$buttonsnap->sink_hooks();
}

?>