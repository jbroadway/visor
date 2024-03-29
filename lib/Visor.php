<?php

/**
 * Abstracts the PHP reflection API and parsing of data for output.
 */
class Visor {
	/**
	 * For now, we're hard-coding our list of libraries.
	 */
	public static $libs = array (
		'Acl',
		'Appconf',
		'Cache',
		'Cli',
		'Controller',
		'CRUD',
		'DB',
		'Debugger',
		'Envconf',
		'ExtendedModel',
		'FileManager',
		'Form',
		'FrontController',
		'I18n',
		'Image',
		'Ini',
		'JobQueue',
		'Link',
		'Lock',
		'Mailer',
		'MemcacheAPC',
		'MemcacheExt',
		'MemcacheRedis',
		'MemcacheXCache',
		'Model',
		'MongoManager',
		'MongoModel',
		'Navigation',
		'Page',
		'Product',
		'Restful',
		'Template',
		'Tree',
		'User',
		'Validator',
		'Versions',
		'View'
	);

	public static $summaries = null;

	/**
	 * Remove the wrapping tags from a comment block.
	 */
	public static function remove_comment_tags ($comment) {
		$comment = preg_replace ('/^\/\*\*?/', '', $comment);
		$comment = preg_replace ('/\*\/$/', '', $comment);
		$comment = preg_replace ('/\n[ \t]+?\* ?/', "\n", $comment);
		return $comment;
	}

	/**
	 * Filter a comment for display.
	 *
	 * - `$comment` - The comment from `Reflection::getDocComment()`
	 * - `$filter` - The output filter, either `markdown` or a custom function
	 *
	 * Returns the filtered comment.
	 */
	public static function filter_comment ($comment, $filter = 'markdown') {
		// remove comment symbols
		$comment = self::remove_comment_tags ($comment);

		// parse @param and @return style elements
		$comment = preg_replace ('/\n@([a-z0-9_]+)(.*)(\$[a-z0-9_]+)/i', "\n* <code>$1</code><code class='value'>$2</code><code class='param'>$3</code>", $comment);
		$comment = preg_replace ('/\n@([a-z0-9_]+)([^\n]+)/i', "\n* <code>$1</code><code class='value'>$2</code>", $comment);

		if ($filter === 'markdown') {
			require_once ('apps/visor/lib/markdown.php');
			$comment = markdown ($comment);
			$comment = str_replace (
				'<pre><code>&lt;',
				'<pre><code class="brush-html">&lt;',
				$comment
			);
			$comment = preg_replace (
				'/\[\[([a-zA-Z0-9_]+)\]\]/',
				'<a href="/api/lib/\1">\1</a>',
				$comment
			);
			return $comment;
		} elseif (is_callable ($filter)) {
			return $filter ($comment);
		}
		return $comment;
	}

	/**
	 * Gets the first sentence from a documentation block.
	 */
	public static function get_short_description ($comment) {
		// remove comment symbols
		$comment = self::remove_comment_tags ($comment);

		// remove newlines
		$comment = preg_replace ('/[\r\n]+/', ' ', $comment);

		// grab the first sentence
		$desc = substr ($comment, 0, strpos ($comment, '.') + 1);

		// filter out common markup elements
		$desc = str_replace (
			array ('[[', ']]', '`'),
			array ('', '', ''),
			$desc
		);

		return $desc;
	}

	/**
	 * Returns a summary of all the classes for use on the index
	 * and sidebar handlers.
	 */
	public static function get_class_summaries () {
		if (self::$summaries !== null) {
			return self::$summaries;
		}

		self::$summaries = array ();
		foreach (self::$libs as $lib) {
			$ref = new ReflectionClass ($lib);
			self::$summaries[$lib] = array (
				'class' => $lib,
				'description' => self::get_short_description ($ref->getDocComment ()),
				'list' => array_merge (
					self::get_properties_summary ($ref),
					self::get_methods_summary ($ref)
				)
			);
		}
		return self::$summaries;
	}

	/**
	 * Gets a summary of properties for a class.
	 */
	public static function get_properties_summary ($ref) {
		$out = array ();
		foreach ($ref->getDefaultProperties () as $name => $value) {
			$prop = $ref->getProperty ($name);
			if ($prop->getDeclaringClass ()->getName () !== $ref->name) {
				continue;
			}
			$out[$prop->name] = array (
				'name' => $prop->name,
				'display' => '$' . $prop->name,
				'type' => 'property'
			);
		}
		return $out;
	}

	/**
	 * Gets a summary of properties for a class.
	 */
	public static function get_methods_summary ($ref) {
		$out = array ();
		foreach ($ref->getMethods () as $method) {
			if ($method->getDeclaringClass ()->getName () !== $ref->name) {
				continue;
			}
			$out[$method->name] = array (
				'name' => $method->name,
				'display' => $method->name . '()',
				'type' => 'method'
			);
		}
		return $out;
	}

	/**
	 * Format a value (property or parameter value) for display.
	 *
	 * - `$value` - The value from `Reflection::getValue ()`
	 *
	 * Returns the formatted value.
	 */
	public static function format_value ($value, $prefix = ' = ') {
		if ($value === null) {
			return '';
		}

		if (is_numeric ($value)) {
			return $prefix . '<span class="value">' . $value . '</span>';
		}

		if ($value === false) {
			return $prefix . '<span class="value">false</span>';
		}

		if ($value === true) {
			return $prefix . '<span class="value">true</span>';
		}

		if (is_array ($value)) {
			$out = $prefix . '<span class="value">array (';
			/*$sep = '';
			foreach ($value as $val) {
				$out .= $sep . self::format_value ($val, '');
				$sep = ', ';
			}*/
			$out .= ')';
			return $out;
		}

		if (is_object ($value)) {
			return $prefix . '<span class="value">' . get_class ($value) . '</span>';
		}

		return $prefix . '<span class="value">\'' . $value . '\'</span>';
	}

	/**
	 * Builds a list of parameter summaries for display.
	 *
	 * - `$params` - The parameter list from `Reflection::getParameters()`
	 *
	 * Returns an array of formatted parameter strings.
	 */
	public static function format_params ($params) {
		$out = array ();
		foreach ($params as $param) {
			$out[] = sprintf (
				'<span class="param">$%s</span>%s',
				$param->getName (),
				$param->isDefaultValueAvailable () ? Visor::format_value ($param->getDefaultValue ()) : ''
			);
		}
		return $out;
	}
	
	/**
	 * Get a list of valid helpers.
	 */
	public static function get_helpers () {
		$files = glob ('apps/*/handlers/util/*.php');
		$helpers = array ();

		foreach ($files as $file) {
			preg_match ('/apps\/(.*)\/handlers\/util\/(.*)\.php$/', $file, $regs);
			$helpers[sprintf ('%s/util/%s', $regs[1], $regs[2])] = null;
		}

		$apps = glob ('apps/*/conf/helpers.php');

		foreach ($apps as $file) {
			$list = parse_ini_file ($file);
			foreach ($list as $helper => $null) {
				$helpers[$helper] = null;
			}
		}

		ksort ($helpers);
		return array_keys ($helpers);
	}
	
	/**
	 * Get docs for a helper.
	 */
	public static function get_helper_docs ($helper) {
		list ($app, $handler) = explode ('/', $helper, 2);
		$route = 'apps/' . $app . '/handlers/' . $handler . '.php';
		
		if (! file_exists ($route)) {
			return false;
		}
		
		// Get the comment itself
		$comments = array_filter (
			token_get_all (file_get_contents ($route)),
			function ($entry) {
				return $entry[0] == T_DOC_COMMENT;
			}
		);

		$comments = array_shift ($comments);
		if (! isset ($comments[1])) {
			return false;
		}

		$docs = $comments[1];

		// remove comment block tags
		$docs = preg_replace ('/^\/\*\*?/', '', $docs);
		$docs = preg_replace ('/\*\/$/', '', $docs);
		$docs = preg_replace ('/\n[ \t]+?\* ?/', "\n", $docs);
		
		return trim ($docs);
	}
	
	/**
	 * Organize helpers by app.
	 */
	public static function helpers_by_app ($helpers = null) {
		$helpers = ($helpers === null) ? self::get_helpers () : $helpers;
		
		// Organize by app
		$by_app = array ();
		foreach ($helpers as $helper) {
			list ($app, $handler) = explode ('/', $helper, 2);
			if (! isset ($by_app[$app])) {
				$name = Appconf::get ($app, 'Admin', 'name');
				$by_app[$app] = array (
					'name' => $name ? $name : $app,
					'helpers' => array ()
				);
			}
			$by_app[$app]['helpers'][] = $helper;
		}
		
		return $by_app;
	}
}

?>
