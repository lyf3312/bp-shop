<?php 
	namespace Home\Controller;

	use Home\Controller\CommonController;
	use Org\Util\Cart;
	/**
	* 购物车控制
	*/
	class CheckoutController extends CommonController
	{
		/**
		 * 添加商品到购物车
		 */	
		public function addCartAction()
		{
			$goods_id = I('request.goods_id');
			$option = I('post.option',[]);
			$quantity = I('quantity','1');
			$cart = new Cart();
			$cart->add($goods_id,$option,$quantity);
			if (I('get.is_ajax') == '1') {
				$this->ajaxReturn(['error'=>'0']);
			}else{
				$this->redirect('Home/Checkout/cart',[],0);
			}
		}
		/**
		 * 获取购物车中数据
		 * @return [type] [description]
		 */	
		public function cartAction()
		{
			$cart = new Cart();
			$goods_list = $cart->getGoodsList();
			$m_goods = D('Goods');
			foreach ($goods_list as &$goods) {
				$row = $m_goods
						->field('name,image_thumb,upc')
						->find($goods['goods_id']);
				$goods = array_merge($goods,$row);

				// 生成合理字符串用于删除
				$o = [];
				foreach ($goods['option'] as $key => $value) {
					$o[] = $key.'-'.$value;
				}
				$goods['option_str'] = implode(',',$o);
				// 计算商品真实价格
				$goods['real_price'] = $m_goods->getRealPrice($goods['goods_id'],$goods['option']);
				// 计算此商品的总价格
				$goods['total_price'] = $goods['real_price'] * $goods['quantity'];
				$total_price += $goods['total_price'];
			}

			// dump($goods_list);
			// die;
			$this->assign('total_price',$total_price);
			$this->assign('total_weight',$cart->getTotalWeight());
			$this->assign('goods_list',$goods_list);
			if (I('post.is_ajax') == 'yes') {
				$this->display('cartContent');
			}else{
				$this->display();				
			}
		}
		
		/**
		 * 判断商品是否有选项
		 * @return boolean [description]
		 */	
		public function hasOptionAction($goods_id)
		{
			$m_ga = M('GoodsAttribute');
			$option = $m_ga
					->where(['goods_id'=>$goods_id,'option'=>'1'])
					->limit(1)
					->count();
			if ($option) {
				$this->ajaxReturn(['result'=>'yes']);
			}else{
				$this->ajaxReturn(['result'=>'no']);
			}
		}
		/**
		 * 移除商品
		 * @return [type] [description]
		 */		
		public function removeGoodsAction()
		{
			$goods_id = I('post.goods_id');
			$option_str = I('post.option_str');
			$cart = new cart();
			// 整理option_str数据
			foreach (explode(',',$option_str) as $str) {
				 $o = explode('-',$str);
				 $option[$o[0]] = $o[1];
			}
			// dump($option);
			// die;
			$cart->remove($goods_id,$option);
			$this->ajaxReturn(['error'=>'0']);
		}
	}
 ?>