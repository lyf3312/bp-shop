<?php 

	namespace Home\Controller;
	use Home\Controller\CommonController;

	/**
	* 前台 商品展示页
	*/
	class ShopController extends CommonController
	{
		/**
		 * 前台默认动作，展示首页
		 * @return [type] [description]
		 */
		 public function indexAction()
		 {
		 	$m_goods = D('Goods');
		 	$where['is_deleted'] = '0';
		 	$where['status'] = '1';
		 	$new_number = CC('Home_new_list_number');
		 	$new_list = $m_goods
		 				->where($where)
		 				->order('create_at DESC')
		 				->limit($new_number)
		 				->select();
		 	
		 	// 添加优惠价
		 	foreach ($new_list as &$goods) {
		 		$goods['real_price'] = $m_goods->getRealPrice($goods['goods_id']);
		 	}
		 	$this->assign('new_list',$new_list);
		 	$this->display();
		 }
		 /**
		  * 商品详情页
		  * @return [type] [description]
		  */
		 public function goodsAction($id)
		 {
		 	$m_goods = D('Goods');
		 	// 商品基本信息
		 	$goods = $m_goods
		 			->alias('g')
		 			->field('g.*,ss.title')
		 			->join('left join __STOCK_STATUS__ as ss using(stock_status_id)')
		 			->find($id);
		 	$this->assign('goods',$goods);
		 	// 获取导航数据
		 	$breadcrumb = $m_goods->getBreadCrumb($id);
		 	$this->assign('breadcrumb',array_reverse($breadcrumb));
		 	// 商品相册
		 	$m_gallery = M('GoodsGallery');
		 	$where['goods_id'] = $goods['goods_id'];
		 	$gallery_list = $m_gallery->where($where)->order('sort_number')->select();
		 	$this->assign('gallery_list',$gallery_list);
		 	// 商品属性数据获取
		 	$m_goods_attr = M('GoodsAttribute');
		 	$goods_attr_list = $m_goods_attr
		 					  ->alias('ga')
		 					  ->field('a.title a_title,at.title at_title,group_concat(av.value) av_value,gav.value gav_value')
		 					  ->join('left join __ATTRIBUTE__ a using(attribute_id)')
		 					  ->join('left join __ATTRIBUTE_TYPE__ at using(attribute_type_id)')
		 					  ->join('left join __GOODS_ATTRIBUTE_VALUE__ gav using(goods_attribute_id)')
		 					  ->join('left join __ATTRIBUTE_VALUE__ av using(attribute_value_id)')
		 					  ->where(['ga.goods_id'=>$goods['goods_id']])
		 					  ->group('ga.attribute_id')
		 					  // ->fetchSql()
		 					  ->select();		 	
		 	$this->assign('goods_attr_list',$goods_attr_list);
		 	//商品属性选项列表的获取
		 	$goods_option_list = $m_goods_attr
		 					  ->alias('ga')
		 					  ->field('ga.option,a.title a_title,a.attribute_id a_attr_id,at.title at_title,group_concat(av.value,\'-\',av.attribute_value_id) av_value_id')
		 					  ->join('left join __ATTRIBUTE__ a using(attribute_id)')
		 					  ->join('left join __ATTRIBUTE_TYPE__ at using(attribute_type_id)')
		 					  ->join('left join __GOODS_ATTRIBUTE_VALUE__ gav using(goods_attribute_id)')
		 					  ->join('left join __ATTRIBUTE_VALUE__ av using(attribute_value_id)')
		 					  ->where(['ga.goods_id'=>$goods['goods_id']])
		 					  ->group('ga.attribute_id')
		 					  // ->fetchSql()
		 					  ->select();
		 		// 格式化数据选项数据
		 		foreach ($goods_option_list as &$option) {
		 			 if (in_array($option['at_title'],['select','select-multiple'])) {
		 			 	 $option['option_list'] = array_map(function($v){
		 			 	 	 return explode('-',$v);
		 			 	 },explode(',',$option['av_value_id'])); 	 	
		 			 }
		 		}
		 	// dump($goods_option_list);
		 	// die;
		 	$this->assign('goods_option_list',$goods_option_list);
		 	$this->display();
		 }
	}
 ?>