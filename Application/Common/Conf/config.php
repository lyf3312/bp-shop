<?php
return array(
	// 默认动作配置
	'DEFAULT_MODULE'        =>  'Home',  // 默认模块
    'DEFAULT_CONTROLLER'    =>  'Shop', // 默认控制器名称
    'DEFAULT_ACTION'        =>  'index', // 默认操作名称
	//'配置项'=>'配置值'
	'ACTION_SUFFIX' => 'Action',
	'LOAD_EXT_CONFIG' => 'db',
	// 配置模板替换常量
	'TMPL_PARSE_STRING'  =>[
		// 上传文件目录
		'__UPLOAD__' => '/Uploads/',
	],
);