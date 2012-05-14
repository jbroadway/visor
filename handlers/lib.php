<?php

$page->layout = $appconf['Visor']['layout'];
$page->add_style ($appconf['Visor']['stylesheet']);

$lib = isset ($this->params[0]) ? $this->params[0] : $this->redirect ('/visor');

$ref = new ReflectionClass ($lib);

$page->title = 'Class: ' . $lib;

$parent = $ref->getParentClass ();
if ($parent) {
	$page->title .= ' extends <a href="/visor/lib/' . $parent->getName () . '">' . $parent->getName () . '</a>';
}

echo '<div class="comment">' . Visor::filter_comment ($ref->getDocComment ()) . '</div>';

echo '<h2>Properties</h2>';

foreach ($ref->getDefaultProperties () as $name => $value) {
	$prop = $ref->getProperty ($name);
	if ($prop->getDeclaringClass ()->getName () !== $lib) {
		continue;
	}
	printf (
		'<div class="visor-block"><h3><code><span class="modifiers">%s</span> <span class="property">$%s</span>%s</code></h3><div class="comment">%s</div></div>',
		implode (' ', Reflection::getModifierNames ($prop->getModifiers ())),
		$prop->name,
		Visor::format_value ($value),
		Visor::filter_comment ($prop->getDocComment ())
	);
}

echo '<h2>Methods</h2>';

foreach ($ref->getMethods () as $method) {
	if ($method->getDeclaringClass ()->getName () !== $lib) {
		continue;
	}
	printf (
		'<div class="visor-block"><h3><code><span class="modifiers">%s</span> <span class="method">%s</span> <span class="params">(%s)</span></code></h3><div class="comment">%s</div></div>',
		implode (' ', Reflection::getModifierNames ($method->getModifiers ())),
		$method->name,
		join (', ', Visor::format_params ($method->getParameters ())),
		Visor::filter_comment ($method->getDocComment ())
	);
}

?>