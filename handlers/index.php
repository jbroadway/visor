<?php

$page->layout = $appconf['Visor']['layout'];
$page->add_style ($appconf['Visor']['stylesheet']);

$page->title = i18n_get ('API Documentation');

echo $tpl->render ('visor/index', array ('libs' => Visor::$libs));

?>