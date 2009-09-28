<?php
	/* 
	 * Test harness for PAT
	 */
	require('lib/cmsfw/cmsfw.class.php');
	require('lib/cmsfw/modules/output/webui.class.php');
	$dir = @'resources/templates/ADO_Classes';
	CMSFW::BuildORMClasses($dir);
	class test { var $cat_id; var $cat_title;}
	$p = new test();
	$p->cat_id = 42;
	$p->cat_title = 'test';
	$t = new Category($p);
	$doc = $t->GetXML();
	$p2 = new test();
	$p2->cat_id = 42;
	$p2->cat_title = 'test ma bitch up';
	$t2 = new Category($p2);
	$doc2 = $t2->GetXML();
	$con = WebUI::ConcatenateXMLDocuments(array($doc, $doc2));
	//var_dump($con);
	echo $con->dump_mem();
?>