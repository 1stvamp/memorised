<?php
	/* 
	 * Scaleable Content Management System Framework
	 * 
	 * Class provides functions to build a CM application
	 * by chaining XML-aware Database/IO Abstraction objects
	 * with XSLT functions and templates, to control via XML
	 * schema the input and output processes of the system.
	 * @author Wesley Mason <w.mason@dcs.hull.ac.uk>
	 * @version 1.0
	 */
	// Switch between PHP4 and PHP5 XSLT functionality
	if (version_compare(PHP_VERSION,'5','>=')&&extension_loaded('xsl')) {
		require_once('xslt-php4-to-php5.php');
	}
	if (version_compare(PHP_VERSION,'5','>=')) {
		require_once('domxml-php4-to-php5.php');
	}
	
	/**
	 * CMSFW package for static CM functions
	 */
	class CMSFW {
		/**
		 * 
		 */
		function BuildORMClasses ($schemaDirectory) {
			if (is_dir($schemaDirectory)) {
				if ($dir = opendir($schemaDirectory) or die('Unable to open schema directory: '.$schemaDirectory)) {
				while (($entry = readdir($dir)) !== false) {
					if (!preg_match('/^\./', $entry)) {
						if (preg_match('/\.schema$/i', $entry)) {
							$schemas[] = CMSFW::ParseORMSchema ($schemaDirectory, $entry);
						}
					}
				}
				closedir($dir);
			}
			}
			if (count($schemas) > 0) {
				foreach($schemas as $schema) {
					extract($schema);
					foreach ($maps as $varName => $map) {
						$i++;
						$vars1 .= "var \$$map;\n";
						$vars2 .= "\$this->$map =& \$obj->$varName;\n";
						$vars2 .= "\$this->varsArray['$map'] = '$map';\n";
						if ($types[$varName]) {
							$vars1 .= 'var $_'.$map."_type;\n";
							$vars2 .= '$this->_'.$map.'_type = \''.$types[$varName].";\n";
						}
					}
					$ex = "class $name extends xmlData {
					$vars1
					function $name(&\$obj) {
					$vars2
					\$this->className = '$name';
					}
					}\n";
					unset($vars1);
					unset($vars2);
					unset($vars3);
					eval ($ex);
				}
			}
		}
		function ParseORMSchema ($schemaDirectory, $entry) {
			$file = file_get_contents($schemaDirectory . '/' . $entry);
			utf8_encode(&$file);
			if(!$xmlDoc = domxml_open_mem($file)) {
				die('Couldn\'t load XML Document: ' . $entry);
			}
			$rootNode = $xmlDoc->document_element();
			if (!($className = $rootNode->get_attribute('name'))) {
				die ('No name attribute specified for root element in ' . $entry);
			}
			if ($columns = $xmlDoc->get_elements_by_tagname('variable')) {
				foreach($columns as $column) {
					$map = $column->get_elements_by_tagname('map');
					$type = $column->get_elements_by_tagname('type');
					if ($map) {
						$mapArray[$column->get_attribute('name')] = $map[0]->get_content();
					} else {
						$mapArray[$column->get_attribute('name')] = $column->get_attribute('name');
					}
					if ($type) {
						$typeArray[$column->get_attribute('name')] = $type[0]->get_content();
					}
				}
			}
			return array (
				'name' => $className,
				'maps' => $mapArray,
				'types' => $typeArray
				);
		}
	}
	/**
	 * XML aware data object
	 */
	class xmlData {
		var $varsArray;
		var $className;
		function GetXML() {
			$docString = '<object></object>';
			$doc = domxml_open_mem($docString);
			$xmlNode = $doc->create_element($this->className);
			foreach ($this->varsArray as $var) {
				$newElement = $doc->create_element($var);
				$varTypeName = '_'.$var.'_type';
				if ($this->$varTypeName) {
					$cdata = $doc->create_cdata_section($this->$var);
					$newElement->append_child($cdata);
				} else {
					$newElement->set_content($this->$var);
				}
				$xmlNode->append_child($newElement);
			}
			$rootNode = $doc->document_element();
			$rootNode->append_child($xmlNode);
			return $doc;
		}
	}
?>