<?php

if (preg_match ('/^\/visor\/helper\/([a-zA-Z0-9\/_-]+)$/', $_SERVER['REQUEST_URI'], $regs)) {
	$open = $regs[1];
} else {
	$open = false;
}

$helpers = Visor::get_helpers ();

echo $tpl->render (
	'visor/helpers-sidebar',
	array (
		'helpers' => $helpers,
		'open' => $open
	)
);

?>