<?php
return array(
	//'配置项'=>'配置值'
	'URL_ROUTER_ON' => True,
	'URL_ROUTE_RULES'=>[
		 'goods/:id\d' => 'Shop/goods',
		 'addCart' => 'Checkout/addCart',
		 'cart' => 'Checkout/cart',
	],
);