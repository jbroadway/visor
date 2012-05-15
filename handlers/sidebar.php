<?php

if (preg_match ('/^\/visor\/lib\/([a-zA-Z0-9_]+)$/', $_SERVER['REQUEST_URI'], $regs)) {
	$open = $regs[1];
} else {
	$open = false;
}

$libs = Visor::get_class_summaries ();

echo $tpl->render (
	'visor/sidebar',
	array (
		'libs' => $libs,
		'open' => $open
	)
);

?>