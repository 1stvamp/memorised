#!/usr/bin/php
<?php

/*
Exaple crontab line:
* 23 * * * ~/delete_old_emails.php > /dev/null 2>&1
*/

function days_between($time1, $time2){ 
	if ($time1 >= $time2){ 
		$time_to_calc1 = $time2;
		$time_to_calc2 = $time1;
	} else { 
		$time_to_calc1 = $time1;
		$time_to_calc2 = $time2;
	} 
	return floor(($time_to_calc2 - $time_to_calc1) / 86400); 
} 

$days_old = 14;
$server = '{host:143/notls}INBOX';
$username = '';
$password = '';
$imap_handle = imap_open($server, $username, $password, OP_SILENT);

$imap_mail_count = imap_num_msg($imap_handle);
for ($i = 1; $i <= $imap_mail_count; $i++) {
	$mail_headers = imap_header($imap_handle, $i);
	if (days_between(time(), strtotime($mail_headers->date)) >= $days_old) {
		imap_delete($imap_handle, $i);
	}
}
imap_expunge($imap_handle);
imap_close($imap_handle);
?>
