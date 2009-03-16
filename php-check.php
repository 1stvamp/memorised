#!/usr/local/bin/php
<?php
// php-check version 1.0p1 (patched by Wesley Mason)
// recursively scans a directory for .php files and runs php -l on
// them (php -l checks for PHP syntax errors)
// revisions at http://glenandpaula.com/wordpress/archives/2007/10/31/scanning-a-directory-for-php-errors/
if (php_sapi_name() != 'cli') {
	die("This utility can only be run from the command line.\n");
}
$counter = 0;
$errors = false;
$php4bin = '/usr/local/bin/php';
$php5bin = '/usr/local/bin/php5';
function scan_dir($dir, $php4=false) {
	$counter = 0;
	$dh=opendir($dir);
	while ($file = readdir($dh)) {
		if ($file=='.' || $file=='..') continue;
		$file = str_replace(' ','\ ', $file);
		$file = str_replace('\\','\\\\', $file);
		if (is_dir($dir.'/'.$file)) {
			$counter += scan_dir($dir.'/'.$file);
		} else {
			if (substr($file, strlen($file) - 4) == '.php') {
				$counter++;
				if ($php4) {
					$output = shell_exec("$php4bin -l $dir/$file 2>&1");
				} else {
					$output = shell_exec("$php5bin -l $dir/$file 2>&1");
				}
				if (substr($output ,0, 2) != 'No') { // skips the "No syntax errors in ..." message
					$errors = true;
					echo $output;
				}
			}
		}
	}
	return $counter;
}
if ($argc!=2 && $argc!=3) {
	die("Usage: php-check [-4] dirname (usually php-check .)\n");
}
if ((!is_dir($argv[1]) && $argv[1] != '-4') || (!is_dir($argv[2]) && $argv[1] == '-4')) {
	die("Argument must be a directory. The most common usage is php-check .\n");
}
if ($argv[1] == '-4') {
	$counter=scan_dir($argv[2], true);
} else {
	$counter=scan_dir($argv[1], false);
}
echo "$counter files checked\n";
exit($errors);
?>
