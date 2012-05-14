<?php

$page->layout = $appconf['Visor']['layout'];
$page->add_style ($appconf['Visor']['stylesheet']);

$page->title = 'API Documentation';

echo $tpl->render ('visor/index', array (
	'libs' => array (
		'Acl',
		'AppTest',
		'Cache',
		'Controller',
		'DB',
		'Debugger',
		'ExtendedModel',
		'Form',
		//'Functions',
		'I18n',
		'Ini',
		'Mailer',
		'MemcacheAPC',
		'MemcacheExt',
		'MemcacheRedis',
		'Model',
		'MongoManager',
		'MongoModel',
		'Page',
		'Restful',
		'Template'
	)
));

?>