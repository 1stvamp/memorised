<?php
/**
 * OpenWordFilter
 */
class OpenWordFilter {
	protected static $blacklist;
	protected static $whitelist;
	protected static $blacklist_regexes;
	protected static $whitelist_regexes;
	protected static $wildcards;
	
	public static function check($text, $class_name=null) {
		if (OpenWordFilter::$whitelist === null) {
			if ($class_name === null) {
				OpenWordFilter::load_whitelist();
			} else {
				call_user_func(array($class_name, 'load_whitelist'));
			}
		}
		if (OpenWordFilter::$blacklist === null) {
			if ($class_name === null) {
				OpenWordFilter::load_wildcards();
				OpenWordFilter::load_blacklist();
			} else {
				call_user_func(array($class_name, 'load_wildcards'));
				call_user_func(array($class_name, 'load_blacklist'));
			}
		}
		list($bl_regexes, $wl_regexes) = OpenWordFilter::get_regexes();
	}
	
	protected static function load_blacklist() {
	}
	
	protected static function load_whitelist() {
	}
	
	protected static function get_regexes($use_cache=true) {
		if (OpenWordFilter::$blacklist_regexes === null) {
			$bl_regexes = array();
			foreach (OpenWordFilter::$blacklist as $part) {
				$regex = '\w(';
				for ($i = 0; $i < strlen($part); $i++) {
					$regex .= '(' . $part[$i] . ')|';
					if (array_key_exists($part[$i], OpenWordFilter::$wildcards)) {
						$regex_parts = array();
						foreach (OpenWordFilter::$wildcards[$part[$i]] as $regex_part) {
							$regex_parts[] = '(' . preg_quote($regex_part) . ')';
						}
						$regex .= implode('|', $regex_parts);
					}
				}
				$regex .= ')\w';
				$bl_regexes[] = $regex;
			}
			OpenWordFilter::$blacklist_regexes = $bl_regexes;
		}
		if (OpenWordFilter::$whitelist_regexes === null) {
			$wl_regexes = array();
			foreach(OpenWordFilter::$whitelist as $part) {
			}
		}
		return array (OpenWordFilter::$blacklist_regexes, OpenWordFilter::$whitelist_regexes);
	}
}
?>