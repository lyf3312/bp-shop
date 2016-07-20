<?php 
	namespace Home\Model;
	use Think\Model;

	/**
	* 商品模型
	*/
	class GoodsModel extends Model
	{
		/**
		 * 获取面包屑导航数据
		 * @return [type] [description]
		 */		
		public function getBreadCrumb($id)
		{
			$category_id = $this->getFieldByGoodsId($id,'category_id');
			if ($category_id == '0') {
				return [];
			}
			$m_category = D('Category');		 
			return $m_category->getSelfAndParents($category_id);
		}

		/**
		 * 获取商品真实价格
		 * @return [type] [description]
		 */
		public function getRealPrice($goods_id,$option_list=[])
		{
			// 1.获取基础价格
			$base_price = $this->getFieldByGoodsId($goods_id,'price');
			// 2.获取优惠价
			$where['goods_id'] = $goods_id;
			$where['date_start'] = ['lt',time()];
			$where['date_end'] = ['gt',time()];
			$price_special = M('PriceSpecial')
							->where($where)
							->limit(1)  
							->find();
			if ($price_special) {
				$price = $price_special['price'];
			}else{
				$price = $base_price;
			}
			// 3.根据特定选项计算价格
			$m_goods_attr = M('GoodsAttribute');
			foreach ($option_list as $attr_id => $attr_value_id) {
				$option_price = $m_goods_attr
							  ->alias('ga')
							  ->field('gav.price_operate,gav.price_drift')
							  ->where([
							  	'ga.attribute_id'=>$attr_id,
							  	'gav.attribute_value_id'=>$attr_value_id,
							  	'ga.goods_id'=>$goods_id])
							  ->join('left join __GOODS_ATTRIBUTE_VALUE__ gav using(goods_attribute_id)')
							  ->limit(1) //没有索引，故使用limit提高速度
							  ->find();
				if ($option_price['price_operate'] == 1) {
					$price += $option_price['price_drift'];
				}else{
					$price -= $option_price['price_drift'];
				}
			}
			return $price;
		}
	}
 ?>