<?php

$page->layout = $appconf['Visor']['helpers_layout'];
$page->add_style ($appconf['Visor']['stylesheet']);

$page->title = i18n_get ('Helpers');

$helpers = Visor::get_helpers ();

echo $tpl->render ('visor/helpers', array ('helpers' => $helpers));

?>