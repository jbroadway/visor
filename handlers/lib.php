<?php

// display settings
$page->layout = $appconf['Visor']['layout'];
$page->add_style ($appconf['Visor']['stylesheet']);

$lib = isset ($this->params[0]) ? $this->params[0] : $this->redirect ('/visor');

$ref = new ReflectionClass ($lib);

$page->title = i18n_get ('Class') . ': ' . $lib;

$parent = $ref->getParentClass ();
if ($parent) {
	$page->title .= ' extends <a href="/visor/lib/' . $parent->getName () . '">' . $parent->getName () . '</a>';
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

echo $tpl->render ('visor/lib', $data);

?>