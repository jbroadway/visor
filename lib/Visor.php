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
		'AppTest',
		'Cache',
		'Controller',
		'DB',
		'Debugger',
		'ExtendedModel',
		'Form',
		'I18n',
		'Ini',
		'Mailer',
		'MemcacheAPC',
		'MemcacheExt',
		'MemcacheRedis',
		'Model',
		'MongoManager',
		'MongoModel',
		'Page',
		'Product',
		'Restful',
		'Template',
		'User',
		'Versions'
	);

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
		$comment = preg_replace ('/^\/\*\*?/', '', $comment);
		$comment = preg_replace ('/\*\/$/', '', $comment);
		$comment = preg_replace ('/\n[ \t]+?\* ?/', "\n", $comment);

		if ($filter === 'markdown') {
			require_once ('apps/visor/lib/markdown.php');
			$comment = markdown ($comment);
			return str_replace (
				'<pre><code>&lt;',
				'<pre><code class="brush-html">&lt;',
				$comment
			);
		} elseif (is_callable ($filter)) {
			return $filter ($comment);
		}
		return $comment;
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
			$sep = '';
			foreach ($value as $val) {
				$out .= $sep . self::format_value ($val, '');
				$sep = ', ';
			}
			$out .= ')';
			return $out;
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
}

?>