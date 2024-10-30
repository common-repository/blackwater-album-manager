function write (text) {
	if(window.opener.tinyMCE) {
		window.opener.tinyMCE.execInstanceCommand('content', 'mceInsertContent', false, text);
	} else {
		window.opener.edInsertContent(window.opener.edCanvas, text);
	}
}
write('this is a test');