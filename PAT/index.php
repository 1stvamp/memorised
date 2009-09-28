<?php
	/**
	 * Loader for the phpBB Archive Tool (PAT)
	 * 
	 * Acts as an "index" file for an httpd to load as
	 * the default file for a directory, to load the PAT
	 * class with default values, session data, or HTTP POST data.
	 * @author Wesley Mason <wes@1stvamp.org>
	 * @see pat.class.php
	 */
	 
	/**
	 * Load the class include file for the archive tool
	 */
	require_once('./lib/pat.class.php');
	// Authentication handling
	require_once('lib/auth.php');
	/**
	 * Define a few configuration settings
	 */
	define ('PAGINATION_LIMIT', 15);
	define ('DEFAULT_OUTPUT_MODE', 'wiki');
	// and instantiate a new copy of the tool object
	$tool = new PAT('mysql', 'wes', 'foobar42p', 'cmdrdata', 'at_test_forum');
	// Set a default value for <var>offset</var> if not set
	$offset = ($_REQUEST['offset']) ? $_REQUEST['offset'] : 0;
	if (!$_REQUEST['action'] || $_REQUEST['action'] == 'listCategories') {
		$tool->Categories();
	}
	if ($_REQUEST['action'] == 'listForums') {
		if ($_REQUEST['categoryID']) {
			$tool->Category($_REQUEST['categoryID']);
		}
	}
	if ($_REQUEST['action'] == 'listTopics' &&
		($_REQUEST['forumID'] || $_REQUEST['posterID'])) {
		$callID = ($_REQUEST['forumID']) ? $_REQUEST['forumID'] : $_REQUEST['posterID'];
		$callType = ($_REQUEST['forumID']) ? 'Forum' : 'Poster';
		if (isset($_REQUEST['topicLimit'])) {
			$tool->Topics($callID, $_REQUEST['topicLimit'], $offset, $callType);
		} else {
			$tool->Topics($callID, PAGINATION_LIMIT, $offset, $callType);
		}
	}
	if ($_REQUEST['action'] == 'displayTopic' &&
		$_REQUEST['topicID']) {
		$tool->Topic($_REQUEST['topicID'], PAGINATION_LIMIT, $offset);
	}
	if ($_REQUEST['action'] == 'output' &&
		$_REQUEST['topicID'] &&
		!$_REQUEST['question']) {
		$tool->Output($_REQUEST['topicID'], DEFAULT_OUTPUT_MODE);
	}
	if ($_REQUEST['action'] == 'output' &&
		$_REQUEST['topicID'] &&
		$_REQUEST['question']) {
		$tool->Output($_REQUEST['topicID'], DEFAULT_OUTPUT_MODE, $_REQUEST['question']);
	}
	// unimplemented
	if ($_REQUEST['action'] == 'buildQuery') {
		
	}
?>
