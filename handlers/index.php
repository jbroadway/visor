<?php

$page->layout = $appconf['Visor']['layout'];
$page->add_style ($appconf['Visor']['stylesheet']);

$page->title = i18n_get ('API Documentation');

$libs = Visor::get_class_summaries ();

echo $tpl->render ('visor/index', array ('libs' => $libs));

?>