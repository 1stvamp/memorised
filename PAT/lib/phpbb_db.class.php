<?php
	require_once('adodb/adodb.inc.php');
	require_once('adodb/adodb-active-record.inc.php');
	require_once('cmsfw/cmsfw.class.php');
	define('CLASS_TEMPLATES_DIRECTORY', @'resources/templates/Classes');
	/**
	 * phpBB Database Abstraction Layer
	 */
	class phpBB_DB  {
		var $_databaseConnection;
		/**
		 * PHP4-style constructor
		 */
		function phpBB_DB($connectionString) {
			$this->_databaseConnection = NewADOConnection($connectionString);
			ADOdb_Active_Record::SetDatabaseAdapter($this->_databaseConnection);
			// Dynamically generate ORM relation classes
			CMSFW::BuildORMClasses(CLASS_TEMPLATES_DIRECTORY);
		}
		/**
		 * PHP5-style constructor
		 */
		function __construct($connectionString) {
			// Calls PHP4-style constructor
			$this->phpBB_DB($connectionString);
		}
		/**
		 * Selects all fields from the category table,
		 * and returns a multiple dimensional array,
		 * sorted by category order
		 */
		function GetCategories() {
			$categories = $this->_databaseConnection->GetActiveRecords('phpbb_categories', 'cat_id=cat_id ORDER BY cat_order');
			for($i = 0; $i < count($categories); $i++) {
				$categoriesArray[$i] = new Category($categories[$i]);
				/*$categoriesArray[$i]->id =& $categories[$i]->cat_id;
				$categoriesArray[$i]->name =& $categories[$i]->cat_title;*/
			}
			return $categoriesArray;
		}
		function GetCategory($categoryID) {
			$category = new ADOdb_Active_Record('phpbb_categories');
			$category->load('cat_id=' . $categoryID);
			return new Category($category);
		}
		/**
		 * Selects all fields from the forums table,
		 * that matches the cat_id <var>categoryID</var>,
		 * and returns a multiple dimensional array,
		 * sorted by forum order, with the category
		 * title as a string at the key 'category'
		 * and the forums as a multidimensional array
		 * at the key 'forums'
		 */
		function GetFora($categoryID) {
			$category = new ADOdb_Active_Record('phpbb_categories');
			$category->load('cat_id=' . $categoryID);
			$fora = $this->_databaseConnection->GetActiveRecords('phpbb_forums', 'cat_id=' . $categoryID . ' ORDER BY forum_order');
			if ($fora != NULL) {
				for($i = 0; $i < count($fora); $i++) {
					$fora[$i] = new Forum($fora[$i]);
				}
				return $fora;
			} else {
				return NULL;
			}
		}
		function GetForum($forumID) {
			$forum = new ADOdb_Active_Record('phpbb_forums');
			$forum->load('forum_id=' . $forumID);
			return new Forum($forum);
		}
		function GetTopic($topicID) {
			$topic = new ADOdb_Active_Record('phpbb_topics');
			$topic->load('topic_id=' . $topicID);
			return new Topic($topic);
		}
		function GetPoster($posterID) {
			$poster = new ADOdb_Active_Record('phpbb_users');
			$poster->load('user_id=' . $posterID);
			return new Poster($poster);
		}
		function GetPostText($postID) {
			$postText = new ADOdb_Active_Record('phpbb_posts_text');
			$postText->load('post_id=' . $postID);
			return new PostText($postText);
		}
		/**
		 * 
		 */
		function GetForumTopics($forumID, $limit, $offset) {
			$limit++;
			$topics = $this->_databaseConnection->GetActiveRecords('phpbb_topics', 'forum_id=' . $forumID . ' ORDER BY topic_last_post_id DESC LIMIT ' . $offset . ',' . $limit);
			//var_dump($topics); // remember: use ->errormsg on _databaseConnection
			if ($topics != NULL) {
				for ($i = 0; $i < count($topics); $i++) {
					if ($i == ($limit-1)) {
						break;
					}
					$topicArray[] = new Topic($topics[$i]);
				}
				if (count($topicArray) < ($limit-1)) {
					$next = false;
				} else {
					$next = true;
				}
				return array (
					'topics' => $topicArray,
					'next' => $next
					);
			} else {
				return NULL;
			}
		}
		/**
		 * 
		 */
		function GetPosterTopics($posterID, $limit, $offset) {
			$limit++;
			$topics = $this->_databaseConnection->GetActiveRecords('phpbb_topics', 'topic_poster=' . $posterID . ' ORDER BY topic_last_post_id DESC LIMIT ' . $offset . ',' . $limit);
			if ($topics != NULL) {
				for ($i = 0; $i < count($topics); $i++) {
					if ($i == ($limit-1)) {
						break;
					}
					$topicArray[$i] = new Topic($topics[$i]);
				}
				if (count($topics) < $limit) {
					$next = false;
				} else {
					$next = true;
				}
				return array (
					'topics' => $topicArray,
					'next' => $next
					);
			} else {
				return NULL;
			}
		}
		/**
		 * 
		 */
		function GetTopicPosts($topicID, $limit, $offset) {
			$limit++;
			$topic = new ADOdb_Active_Record('phpbb_topics');
			if ($topic->load('topic_id=' . $topicID)) {
			if ($limit != 1) {
					$posts = $this->_databaseConnection->GetActiveRecords('phpbb_posts', 'topic_id=' . $topicID . ' ORDER BY post_time LIMIT ' . $offset . ',' . $limit);
				} else {
					$posts = $this->_databaseConnection->GetActiveRecords('phpbb_posts', 'topic_id=' . $topicID . ' ORDER BY post_time');
				}
				for ($i = 0; $i < count($posts); $i++) {
					if ($i == ($limit-1) && $limit != 1) {
						break;
					}
					$post = new ADOdb_Active_Record('phpbb_posts_text');
					$post->load('post_id=' . $post->post_id);
					$postsArray[$i] = new Post($posts[$i]);
				}
				if (count($posts) < $limit) {
					$next = false;
				} else {
					$next = true;
				}
				return array (
					'posts' => $postsArray,
					'next' => $next
					);
			} else {
				return NULL;
			}
		}
		function GetMatchedTopics ($text) {
			$matchedWords = $this->_databaseConnection->GetActiveRecords('phpbb_search_wordlist', 'word_text=\'' . strtolower($text) . '\'');
			if ($matchedWords != NULL) {
				foreach ($matchedWords as $word) {
					$wordMatches = $this->_databaseConnection->GetActiveRecords('phpbb_search_wordmatch', 'word_id=', $word->word_id);
					if ($wordMatches != NULL) {
						foreach ($wordMatches as $match) {
							$post = new ADOdb_Active_Record('phpbb_posts');
							$post->load('post_id=', $match->post_id);
							$topic = new ADOdb_Active_Record('phpbb_topics');
							$topic->load('topic_id=', $post->topic_id);
							$matchedTopicsArray[] = $topic;
						}
					}
				}
			}
			return $matchedTopicsArray;
		}
	}
?>