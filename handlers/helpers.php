<?php

$page->layout = $appconf['Visor']['helpers_layout'];
$page->add_style ($appconf['Visor']['stylesheet']);

$page->title = i18n_get ('Helpers');

$helpers = Visor::helpers_by_app ();

echo $tpl->render ('visor/helpers', array ('helpers' => $helpers));

?>