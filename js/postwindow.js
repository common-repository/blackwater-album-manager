var bamWriteWindow;

function bamOpenWriteWindow (wpurl) {
	bamWriteWindow = window.open(wpurl + '/wp-content/plugins/blackwater/write-page.php?inalbum=/', 'bamWritePost', 'width=500,height=500,scrollbars=yes,resizable=yes');
}