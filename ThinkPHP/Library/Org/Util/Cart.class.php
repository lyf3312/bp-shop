<?php 
	namespace Org\Util;



	/**
	* 购物车相关操作的类
	*/
	class Cart
	{
		// 商品列表，数据结构为:
		// [
		// 	 ['goods_id'=>11,
		// 	  'option'=>['attr_id1'=>'attr_value_id1','attr_id2'=>'attr_value_id2',...],
		// 	  'quantity'=>222],
		// 	 [ ... ],
		// ]
		private $goods_list = []; //购物车中商品
		private $total_weight = 0; //购物车中商品总重量[g]
		
		public function __construct()
		{
			// 初始化时，获取购物车中数据
			$this->get();
			// 计算总重量
			$this->setTotalWeight();
		}
		
		/**
		 * 添加商品到购物车
		 * @param int $goods_id [description]
		 * @param array   $option   [description]
		 * @param integer $quantity [description]
		 */	
		public function add($goods_id,$option=[],$quantity=1)
		{
		   // 先判断是否为同一件商品，然后添加
		   $goods_flag = false; //默认不是同一件商品
			foreach ($this->goods_list as $key => $goods) {
				if ($goods['goods_id'] == $goods_id) {
					 // 是同一件商品，在判断选项一样否，选项相同才属于完全一样的商品
					 $option_flag = true; //默认是同一款商品
					 foreach ($goods['option'] as $goods_attr_id => $goods_attr_value_id) {
					  	 if ($goods_attr_value_id !== $option[$goods_attr_id]) {
					  	 		$option_flag = false;
					  	 		continue 2;
					  	 }
					 }

					 // 商品选项都相同，则记录当前商品数量加1
					 if ($option_flag) {
					 	$goods_flag = true; //是同一件且同款商品
					 	$goods_key = $key; //记录这件商品在购物车商品列表中的key
					 	break;
					 }
				}
			}

			// 判断商品是否是同个同款
			if ($goods_flag) {
				// 同个同款
				$this->goods_list[$goods_key]['quantity'] += $quantity;
			}else{
				// 不同个同款
				$this->goods_list[] =[
					'goods_id' => $goods_id,
					'option' => $option,
					'quantity' => $quantity,
				];
			}
			// 计算总重
			$this->setTotalWeight();
			// 持久化
			$this->save();
			return $this;  //返回对象，便于连贯操作
		}

		/**
		 * 持久化购物车数据
		 * @return [type] [description]
		 */	
		public function save()
		{
			// 判断用户是否登录		
			if (session('user')) {
				// 写入数据库	
			}else{
				// 存于cookie中
				cookie('cart_goods_list',serialize($this->goods_list));	
			}

		}
		/**
		 * 获取购物车中的数据
		 * @return [type] [description]
		 */
		public function get()
		{
			if (session('user')) {
				// 数据库中读取
			}else{
				// 判断购物车数据是否存在
				if ($cart_goods_list = cookie('cart_goods_list')) {
					$this->goods_list = unserialize($cart_goods_list);
				}else{
					$this->goods_list = [];
				}
			}
		}

		/**
		 * 获取商品列表
		 * @return [type] [description]
		 */
		public function getGoodsList()
		{
			return $this->goods_list;
		}

		/**
		 * 获取商品总重量
		 * @return [type] [description]
		 */
		public function getTotalWeight()
		{
			return $this->total_weight;
		}

		/**
		 * 计算商品总重量
		 */
		public function setTotalWeight()
		{
			$m_goods = M('Goods');
			foreach ($this->goods_list as $goods) {
		 		$weight =	$m_goods
		 				->field('weight,wu.title unit')
		 				->join('left join __WEIGHT_UNIT__ wu using(weight_unit_id)')
		 				->where(['goods_id'=>$goods['goods_id']])
		 				->find();
		 	    if ($weight['unit'] == '千克') {
		 	    	 $weight['weight'] *= 1000;
		 	    }else if($weight['unit']== '500克(斤)'){
		 	    	 $weight['weight'] *= 500;
		 	    }
		 	    $this->total_weight += $weight['weight'] * $goods['quantity'];
			}
			return $this;
		}
		/**
		 * 删除购物车中商品
		 * @param  [type] $goods_id   [description]
		 * @param  [type] $option_str [description]
		 * @return [type]             [description]
		 */
		public function remove($goods_id,$option)
		{
		   // 先判断是否为同一件商品，然后添加
		   $goods_flag = false; //默认不是同一件商品
			foreach ($this->goods_list as $key => $goods) {
				if ($goods['goods_id'] == $goods_id) {
					 // 是同一件商品，在判断选项一样否，选项相同才属于完全一样的商品
					 $option_flag = true; //默认是同一款商品
					 foreach ($goods['option'] as $goods_attr_id => $goods_attr_value_id) {
					  	 if ($goods_attr_value_id !== $option[$goods_attr_id]) {
					  	 		$option_flag = false;
					  	 		continue 2;
					  	 }
					 }

					 // 商品选项都相同，则记录当前商品数量加1
					 if ($option_flag) {
					 	$goods_flag = true; //是同一件且同款商品
					 	$goods_key = $key; //记录这件商品在购物车商品列表中的key
					 	break;
					 }
				}
			}
			// 判断商品是否是同个同款
			if ($goods_flag) {
				// 找到此商品，删除
				unset($this->goods_list[$goods_key]);
				$this->goods_list = array_values($this->goods_list);
				
			}
			// 计算总重
			$this->setTotalWeight();
			// 持久化
			$this->save();
			return $this;  //返回对象，便于连贯操作
		}
	}
 ?>