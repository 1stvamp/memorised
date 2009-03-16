<?php
	require_once('phpbb_db.class.php');
	require_once('cmsfw/modules/output/webui.class.php');
	/**
	 * phpBB Archive Tool Class
	 * 
	 * The class to create an object for
	 * running queries against a phpBB forum database
	 * and generating an archive of selected threads
	 * from the forum. Default output of the archive
	 * is a simple 'wiki'-format web-site.
	 * @author Wesley Mason <w.mason@dcs.hull.ac.uk>
	 * @see phpbb_db.class.php
	 * @version 1.0 (release)
	 */
	define('PAGE_TITLE', 'phpBB Archive Tool');
	define('COMMON_LINKS', '<p class="actions"><a href="?action=listCategories">List Categories</a> - <a href="?action=help">Help</a> - <a href="?action=logout">Log out</a></p>');
	class PAT {
		var $_databaseConnectionProperties;
		var $_database;
		
		/**
		 * PHP4-style constructor
		 */
		function PAT($type, $username, $password,
			$hostname, $database, $port=NULL) {
			$this->_databaseConnectionProperties = array (
				'type'=>$type,
				'username'=>$username,
				'password'=>$password,
				'hostname'=>$hostname,
				'database'=>$database,
				'port'=>$port
			);
			$connectionString = $this->BuildConnectionString();
			$this->_database = new phpBB_DB($connectionString);
		}
		
		/**
		 * PHP5-style constructor
		 */
		function __construct($type, $username, $password,
			$hostname, $database, $port=NULL) {
			// Calls the PHP4-style constructor
			$this->PAT($type, $username, $password,
				$hostname, $database, $port);
		}
		/**
		 * Builds  a valid ADO connection string
		 * 
		 * Generates a valid ADO connection string
		 * from the database connection properties array
		 */
		function BuildConnectionString() {
			extract($this->_databaseConnectionProperties);
			// Build and return an ADO connection string
			return ($port == NULL) ? $type . '://' .
				$username . ':' . $password . '@' . $hostname . '/' .
				$database : $type . '://' . $username . ':' . $password .
				'@' . $hostname . ':' . $port . '/' . $database;
		}
		function Categories() {
			$categories =& $this->_database->GetCategories();
			$xslt = xslt_create();
			$xml = WebUI::GetAndConcatenateXMLDocuments($categories);
			$arguments['/_xml'] = $xml->dump_mem();
			$cdata = xslt_process($xslt, 'arg:/_xml', 'resources/templates/UI/Categories.xsl', NULL, $arguments);
			$cdata = COMMON_LINKS . $cdata;
			$output = WebUI::GenerateContainerDocumentXML(PAGE_TITLE, $cdata);
			$arguments['/_xml'] = $output->dump_mem();
			echo xslt_process($xslt, 'arg:/_xml', 'resources/templates/UI/XHTMLPage.xsl', NULL, $arguments);
		}
		function Category($categoryID) {
			$category =& $this->_database->GetCategory($categoryID);
			$fora =& $this->_database->GetFora($categoryID);
			$xslt = xslt_create();
			$merge = array_merge(array($category), $fora);
			$xml = WebUI::GetAndConcatenateXMLDocuments($merge);
			$arguments['/_xml'] = $xml->dump_mem();
			$cdata = xslt_process($xslt, 'arg:/_xml', 'resources/templates/UI/Category.xsl', NULL, $arguments);
			$cdata = COMMON_LINKS . $cdata;
			$output = WebUI::GenerateContainerDocumentXML(PAGE_TITLE, $cdata);
			$arguments['/_xml'] = $output->dump_mem();
			echo xslt_process($xslt, 'arg:/_xml', 'resources/templates/UI/XHTMLPage.xsl', NULL, $arguments);
		}
		function Topics($id, $limit, $offset, $type) {
			if (!is_int($limit) && $limit <= 0) {
				die ('Limit of \'' . $limit . '\' is not an integer greater than 0.');
			}
			$topicsCallback = 'Get'.$type.'Topics';
			extract($topics =& $this->_database->$topicsCallback($id, $limit, $offset));
			$posters = array();
			if ($type == 'Forum') {
				$forum =& $this->_database->GetForum($id);
				$name =& $forum;
			} else {
				$poster = $this->_database->GetPoster($id);
				$posters[$poster->name] =& $poster;
				$name =& $posters[$poster->name];
			}
			$category =& $this->_database->GetCategory($forum->category_id);
			
			if ($type == 'Forum') {
				foreach ($topics as $topic) {
					$poster =& $this->_database->GetPoster($topic->poster_id);
					if (!(array_key_exists($poster->name, $posters))) {
						$posters[$poster->name] = $poster;
					}
				}
			}
			$xslt = xslt_create();
			$merge = array_merge(array($category), array($name), $topics, $posters);
			$xml = WebUI::GetAndConcatenateXMLDocuments($merge);
			$arguments['/_xml'] = $xml->dump_mem();
			$cdata = xslt_process($xslt, 'arg:/_xml', 'resources/templates/UI/'.$type.'Topics.xsl', NULL, $arguments);
			if ($offset > 0) {
				$previous_link = '<a href="?action=listTopics&amp;'.strtolower($type).'ID=' .$id . '&amp;limit=' . $limit . '&amp;offset=' . ($offset - $limit) . '" class="previous">&lt;- Previous ' . $limit . ' topics</a>&nbsp;';
			}
			if ($next) {
				$next_link = '&nbsp;<a href="?action=listTopics&amp;'.strtolower($type).'ID=' . $id . '&amp;limit=' . $limit . '&amp;offset=' . ($offset + $limit) . '" class="next">Next ' . $limit . ' topics -&gt;</a>';
			}
			$cdata = COMMON_LINKS . $cdata . $previous_link . $next_link;
			$output = WebUI::GenerateContainerDocumentXML(PAGE_TITLE, $cdata);
			$arguments['/_xml'] = $output->dump_mem();
			echo xslt_process($xslt, 'arg:/_xml', 'resources/templates/UI/XHTMLPage.xsl', NULL, $arguments);
		}
		function Topic($id, $limit, $offset=0, $outputMode='Topic') {
			if (!is_int($limit) && $limit <= 0 && $outputMode == 'Topic') {
				die ('Limit of \'' . $limit . '\' is not an integer greater than 0.');
			}
			require_once ('./lib/BBCode/bbcode.php');
			
			extract($posts =& $this->_database->GetTopicPosts($id, $limit, $offset));
			$topic =& $this->_database->GetTopic($id);
			$forum =& $this->_database->GetForum($topic->forum_id);
			
			$posters = array();
			$postText = array();
			foreach ($posts as $key => $post) {
				$postText[$key] =& $this->_database->GetPostText($post->id);
				$poster =& $this->_database->GetPoster($post->poster_id);
				if (!(array_key_exists($poster->name, $posters))) {
					$posters[$poster->name] = $poster;
				}
				if ($post->html) {
					if ($post->bbcode) {
						$postText[$key]->text = bbencode_second_pass(nl2br(htmlspecialchars($post['text'])), $postText[$key]->bbcode_uid);
					} else {
						$postText[$key]->text = nl2br(htmlspecialchars($postText[$key]->text));
					}
				} else {
					if ($post->bbcode) {
						$postText[$key]->text = bbencode_second_pass(nl2br($postText[$key]->text), $postText[$key]->bbcode_uid);
					} else {
						$postText[$key]->text = nl2br($postText[$key]->text);
					}
				}
			}
			
			$xslt = xslt_create();
			$merge = array_merge(array($forum), array($topic), $posts, $postText, $posters);
			$xml = WebUI::GetAndConcatenateXMLDocuments($merge);
			$arguments['/_xml'] = $xml->dump_mem();
			// Switch to the UI or Output template directories
			if ($outputMode != 'Topic') {
				$outputXSL = 'Output/' . $outputMode;
			} else {
				$outputXSL = 'UI/' . $outputMode;
			}
			$cdata = xslt_process($xslt, 'arg:/_xml', 'resources/templates/'.$outputXSL.'.xsl', NULL, $arguments);
			if ($outputMode != 'Topic') {
				return $cdata;
			}
			if ($offset > 0) {
				$previous_link = '<a href="?action=displayTopic&amp;topicID=' .$id . '&amp;limit=' . $limit . '&amp;offset=' . ($offset - $limit) . '" class="previous">&lt;- Previous ' . $limit . ' topics</a>&nbsp;';
			}
			if ($next) {
				$next_link = '&nbsp;<a href="?action=displayTopic&amp;topicID=' . $id . '&amp;limit=' . $limit . '&amp;offset=' . ($offset + $limit) . '" class="next">Next ' . $limit . ' topics -&gt;</a>';
			}
			$cdata = COMMON_LINKS . $cdata . $previous_link . $next_link;
			$output = WebUI::GenerateContainerDocumentXML(PAGE_TITLE, $cdata);
			$arguments['/_xml'] = $output->dump_mem();
			if ($outputMode == 'Topic') {
				echo xslt_process($xslt, 'arg:/_xml', 'resources/templates/UI/XHTMLPage.xsl', NULL, $arguments);
			}
		}
		// Unimplemented
		function BuildQuery($posterName=NULL, $topicName=NULL, $categoryID=NULL, $forumID=NULL,
			$queryPreset=NULL, $text=NULL, $limit=NULL) {
			if ($topicName) {
				$topic = $this->_database->GetTopicPosts(NULL, $topicName);
			}
			if ($posterName) {
				$posterTopics = $this->_database->GetPosterTopics(NULL, NULL, $postName);
			}
		}
		function Output($id, $mode, $question=NULL) {
			// Strip out slashes and quotation marks,
			// and replace spaces with underscores
			$msgXML = new OutputMessage($id, $question, NULL, NULL);
			if ($question) {
				$msgXML->question = $question;
				$question = stripslashes(str_replace('"', '', str_replace(' ', '_',$question)));
				if (!file_exists('output/wiki/data/'.$question)) {
					$output =& $this->Topic($id, 0, 0, 'Wiki');
					$file = fopen('output/wiki/data/'.$question, 'w+');
					fwrite($file, $output);
					fclose($file);
					$msg = 'Question successfully added to the wiki!';
					$msgXML->done = 1;
				} else {
					$msg = 'Question already exists in wiki, please choose another question title.';
				}
			} else {
				$msg = 'Please choose a question name for this topic to add to the wiki.';
			}
			$msgXML->message = $msg;
			$msgXML->id = $id;
			$msgXML->question = $question;
			$xml = $msgXML->GetXML();
			$xslt = xslt_create();
			$arguments['/_xml'] = $xml->dump_mem();
			$cdata = xslt_process($xslt, 'arg:/_xml', 'resources/templates/UI/Output.xsl', NULL, $arguments);
			$cdata = COMMON_LINKS . $cdata;
			$output = WebUI::GenerateContainerDocumentXML(PAGE_TITLE, $cdata);
			$arguments['/_xml'] = $output->dump_mem();
			echo xslt_process($xslt, 'arg:/_xml', 'resources/templates/UI/XHTMLPage.xsl', NULL, $arguments);
		}
	}
	class OutputMessage extends xmlData {
		var $id;
		var $question;
		var $done;
		var $message;
		
		function OutputMessage($id, $question, $done, $message) {
			$this->id = $id;
			$this->varsArray['id'] = 'id';
			$this->question = $question;
			$this->varsArray['question'] = 'question';
			$this->done = $done;
			$this->varsArray['done'] = 'done';
			$this->message = $message;
			$this->varsArray['message'] = 'message';
			
			$this->className = 'OutputMessage';
		}
	}
?>