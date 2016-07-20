<?php 
	namespace Back\Model;
	use Think\Model;

	/**
	* 后台商品模型
	*/
	class GoodsModel extends Model
	{
		
		 // 自动完成
		protected $_auto = [
			['create_at','time',self::MODEL_INSERT,'function'],
			['update_at','time',self::MODEL_BOTH,'function'],
			['is_deleted','0',self::MODEL_INSERT],
		];
		// 自动验证
		protected $_validate = [
			['name','require','商品名称是必须的'],
			['price','require','商品价格是必须的'],
			['price','number','商品价格必须是数字',0],
			['quantity','number','商品数量必须是数字',0],
			['width','number','宽度必须是数字',2],
			['heigth','number','高度必须是数字',2],
			['length','number','长度必须是数字',2],
			['weight','number','重量必须是数字',2],
			['sort_number','number','排序必须是数字',0],
		];
	}
 ?>