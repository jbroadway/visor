<?php

$page->layout = $appconf['Visor']['layout'];
$page->add_style ($appconf['Visor']['stylesheet']);

$helper = join ('/', $this->params);
$helpers = Visor::get_helpers ();

if (! in_array ($helper, $helpers)) {
	$this->redirect ('/visor/helpers');
}

$page->title = i18n_get ('Helper') . ': ' . Template::sanitize ($helper);

$docs = Visor::get_helper_docs ($helper);

if (! $docs) {
	printf (
		'<p>%s</p><p><a href="/visor/helpers">%s</a></p>',
		__ ('No documentation found.'),
		__ ('Continue')
	);
	return;
}

$docs = Visor::filter_comment ($docs);

echo $tpl->render ('visor/helper', array ('docs' => $docs));
