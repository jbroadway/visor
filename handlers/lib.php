<?php

// display settings
$page->layout = $appconf['Visor']['layout'];
$page->add_style ($appconf['Visor']['stylesheet']);

$lib = isset ($this->params[0]) ? $this->params[0] : $this->redirect ('/visor');

$ref = new ReflectionClass ($lib);

$page->title = i18n_get ('Class') . ': ' . $lib;

$parent = $ref->getParentClass ();
if ($parent) {
	$page->title .= ' extends <a href="/api/lib/' . $parent->getName () . '">' . $parent->getName () . '</a>';
}

// caching
if (! file_exists ('cache/visor')) {
	mkdir ('cache/visor');
	chmod ('cache/visor', 0777);
}
$cache_file = 'cache/visor/' . $ref->getName () . '.html';
if (file_exists ($cache_file) && filemtime ($cache_file) >= filemtime ($ref->getFileName ())) {
	echo file_get_contents ('cache/visor/' . $ref->getName () . '.html');
	return;
}

$data = array (
	'class_comment' => Visor::filter_comment ($ref->getDocComment ()),
	'property_count' => 0,
	'properties' => array (),
	'method_count' => 0,
	'methods' => array ()
);

// build properties
foreach ($ref->getDefaultProperties () as $name => $value) {
	$prop = $ref->getProperty ($name);
	if ($prop->getDeclaringClass ()->getName () !== $lib) {
		continue;
	}
	$data['properties'][] = array (
		'name' => $prop->name,
		'title' => sprintf (
			'<span class="modifiers">%s</span> <span class="property">$%s</span>%s',
			implode (' ', Reflection::getModifierNames ($prop->getModifiers ())),
			$prop->name,
			Visor::format_value ($value)
		),
		'comment' => Visor::filter_comment ($prop->getDocComment ())
	);
	$data['property_count']++;
}

//build methods
foreach ($ref->getMethods () as $method) {
	if ($method->getDeclaringClass ()->getName () !== $lib) {
		continue;
	}
	$data['methods'][] = array (
		'name' => $method->name,
		'title' => sprintf (
			'<span class="modifiers">%s</span> <span class="method">%s</span> <span class="params">(%s)</span>',
			implode (' ', Reflection::getModifierNames ($method->getModifiers ())),
			$method->name,
			join (', ', Visor::format_params ($method->getParameters ()))
		),
		'comment' => Visor::filter_comment ($method->getDocComment ())
	);
	$data['method_count']++;
}

// render and cache output
$res = $tpl->render ('visor/lib', $data);
file_put_contents ('cache/visor/' . $ref->getName () . '.html', $res);
chmod ('cache/visor/' . $ref->getName () . '.html', 0777);
echo $res;

?>