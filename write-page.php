<?php
define('WP_USE_THEMES', false);
require '../../../wp-blog-header.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Add Pictures</title>
<link type="text/css" rel="stylesheet" href="styles/write-post.css" />
<script type="text/javascript">
function write (text) {
	if(window.opener.tinyMCE) {
		window.opener.tinyMCE.execInstanceCommand('content', 'mceInsertContent', false, text);
	} else {
		window.opener.edInsertContent(window.opener.edCanvas, text);
	}
}
</script>
</head>
<body>
<?php
$write_bam_album = bam_album($_GET['inalbum']);
echo '<table class="albums" cellspacing="10">'."\n";
$bam_td = 0;
echo "<tr>\n";
while ($write_bam_album->the_album()) {
	if (!($bam_td % 3) && $bam_td) echo '</tr>';
	if (!($bam_td % 3) && $bam_td) echo '<tr>';
	$bam_td++;
	echo '<td><a href="write-page.php?inalbum='.$write_bam_album->album_path(true).'"><img src="images/folder.gif" /></a><br />';
	echo $write_bam_album->album_foldername();
	echo "</td>\n";
}
if ($bam_td-1 % 3 && $bam_td) echo "</tr>\n";
echo '</table>';
echo "\n<ul class=\"pictures\">\n";
while ($write_bam_album->the_picture()) {
	echo "\t<li>".
	$write_bam_album->picture_filename(true)
	."(<a href=\"javascript:write('{{bamPicture}}{{src:".$write_bam_album->picture_filename(true)."}}{{/bamPicture}}');\">insert</a>)</li>\n";
}
echo "</ul>\n";
?>
</body>
</html>