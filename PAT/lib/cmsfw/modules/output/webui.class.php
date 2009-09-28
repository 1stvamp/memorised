<?php
	/* 
	 * Web-based User Interface output module for the CMSFW package
	 */
	require_once('lib/cmsfw/cmsfw.class.php');
	class WebUI {
		function GenerateContainerDocumentXML($name, $content) {
			$docString = '<outputDoc></outputDoc>';
			$doc = domxml_open_mem($docString);
			$rootNode = $doc->document_element();
			$rootNode->set_attribute('name', $name);
			$contentNode = $doc->create_element('content');
			$cdata = $doc->create_cdata_section($content);
			$contentNode->append_child($cdata);
			$rootNode->append_child($contentNode);
			return $doc;
		}
		function ConcatenateXMLDocuments($docArray) {
			$docString = '<objects></objects>';
			$docs = domxml_open_mem($docString);
			$rootNode = $docs->document_element();
			foreach ($docArray as $doc) {
				$nodeArray = $doc->child_nodes();
				foreach ($nodeArray as $node) {
					$temp = $docs->create_element('import');
					$temp = $node->clone_node(true);
					$rootNode->append_child($temp);
				}
			}
			return $docs;
		}
		function GetAndConcatenateXMLDocuments($objectArray) {
			foreach ($objectArray as $object) {
				$doc = $object->GetXML();
				$xmlArray[] = $doc;
			}
			return WebUI::ConcatenateXMLDocuments($xmlArray);
		}
	}
?>