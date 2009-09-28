<?php
/**
 * Basic Wiki Output Script
 *
 * Reads a directory of text files and outputs
 * an FAQ list from the filenames.
 * Selecting a link loads the contents of
 * the files. If logged in as an admin,
 * user can edit files.
 */
require_once('../../lib/auth.php');
define('WIKI_DATA_DIR', 'data');
print '<kbd>authenticated as wesleymason@localhost (local domain)</kbd><br /><br />';
if ($_REQUEST['page'] && !$_REQUEST['action']) {
	$_REQUEST['page'] = stripslashes($_REQUEST['page']);
	if (file_exists(WIKI_DATA_DIR . '/' . $_REQUEST['page'])) {
		print '<a href="index.php?page='.$_REQUEST['page'].'&amp;action=edit">Edit page</a><br /><br />';
		readfile(WIKI_DATA_DIR . '/' . $_REQUEST['page']);
	} else {
		echo 'File not found.';
	}
} else if ($_REQUEST['action']=='edit') {
	echo '<form>';
	echo '<textarea cols="35" rows="15" wrap="virtual" name="content">';
	readfile(WIKI_DATA_DIR . '/' . $_REQUEST['page']);
	echo '</textarea>';
	echo '<input type="hidden" name="page" value="'.$_REQUEST['page'].'" />';
	echo '<input type="hidden" name="action" value="commit" />';
	echo '<input type="submit"/>';
	echo '</form>';
} else if ($_REQUEST['action']=='commit') {
	$file = fopen(WIKI_DATA_DIR . '/' . $_REQUEST['page'], 'w+');
					fwrite($file, $_REQUEST['content']);
					fclose($file);
	echo 'Changes commited, <a href="index.php?page='.$_REQUEST['page'].'">back</a>?';
} else {
	if (is_dir(WIKI_DATA_DIR)) {
	if ($dir = opendir(WIKI_DATA_DIR) or die('Unable to open wiki data directory: '.WIKI_DATA_DIR)) {
	while (($entry = readdir($dir)) !== false) {
		if (!preg_match('/^\./', $entry)) {
			echo '<li><a href="?page='.urlencode($entry).'">'.str_replace('_', ' ', $entry).'</a></li>';
		}
	}
	closedir($dir);
	}
	}
}
?>