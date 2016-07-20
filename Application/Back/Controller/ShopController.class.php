<?php 
	namespace Back\Controller;
	use Think\Controller;
	use Think\Upload;
	use Think\Image;
	use Org\Util\Page;
	/**
	* 商品控制器
	*/
	class ShopController extends Controller
	{
		/**
		 * 展示商品列表
		 * @return [type] [description]
		 */		
		public function listAction()
		{
			// 获取数据
			$m_goods = M('Goods');
			$page = I('get.p',1); //获取当前页，默认为1;
			$pageSize = CC('back_goods_list_pagesize');
			$where['is_deleted'] = '0';
			$search_cond = [];
			// 查询条件判断
			//商品名称查询
			if (($filter_name = I('get.filter_name','','trim')) !== '') {
				$where['name'] = ['like',$filter_name.'%'];
				$this->assign('filter_name',$filter_name);
				$search_cond['filter_name'] = $filter_name;
			}
			// 区间查询
			if (($filter_price_min = I('get.filter_price_min','','trim'))!== '') {
				if (is_numeric($filter_price_min)) {
					$where['price'][] = ['egt',$filter_price_min];
					$this->assign('filter_price_min',$filter_price_min);
					$search_cond['filter_price_min'] =$filter_price_min;
				}
			}
			if (($filter_price_max = I('get.filter_price_max','','trim'))!== '') {
				if (is_numeric($filter_price_max)) {
					$where['price'][] = ['elt',$filter_price_max];
					$this->assign('filter_price_max',$filter_price_max);
					$search_cond['filter_price_max'] = $filter_price_max;
				}
			}
			// 状态查询
			if (($filter_status = I('get.filter_status','','trim')) !== '') {
				 $where['status'] =['eq',$filter_status];
				 $this->assign('filter_status',$filter_status);
				 $search_cond['filter_status'] = $filter_status;
			}
			// 排序方式处理
			if ($_GET['order_field']) {		
				 $order = "{$_GET['order_field']} {$_GET['order_method']}";
				// 商品排序后，记录在查找条件中，然后加入翻页url链接中
				 $search_cond['order_field'] =$_GET['order_field'];
				 $search_cond['order_method'] = $_GET['order_method'];

			}else{
				$order='create_at desc';
			}
			$goods_list = $m_goods
						->where($where)
						->page($page,$pageSize)
						->order($order)
						// ->fetchSql()
						->select();
			$this->assign('goods_list',$goods_list);
			// 将当前页码添加到排序搜索条件中
			$search_cond['p'] = $page;
			$this->assign('search_cond',$search_cond);
			// 分页
			$total = $m_goods->where($where)->count();
			$t_page = new Page($total,$pageSize);
			$t_page->parameter = $search_cond;
			$this->assign('page_html',$t_page->show());
			$this->display();
		}
		/**
		 * 商品列表展示及添加
		 */		
		public function addAction()
		{
		 	if (IS_POST) {	
		 		$m_goods =D('Goods');
		 		$res = $m_goods->create();
		 		if ($res) {
			//1. 插入基本数据
		 			$goods_id = $m_goods->add();
		 			if ($goods_id) {
			//2. 插入相册
		 				$m_gallery = M('GoodsGallery');
		 				$gallery_list = I('post.gallery');
		 				// 循环为每条数据添加goods_id;
		 				foreach ($gallery_list as &$gallery) {
		 					$gallery['goods_id'] = $goods_id;
		 				}
		 				// dump($gallery_list);
		 				// die;
		 				$m_gallery->addAll($gallery_list);
			//3. 更新商品类型数据
		 				$m_goods->goods_type_id = I('post.type_id');//ORM格式数据
		 				$m_goods->save();
			//4. 更新属性
		 				$attr_list = I('post.attribute');
		 				$m_goods_attr = M('GoodsAttribute');
		 				$m_attr_type = M('AttributeType');
		 				$m_goods_attr_value = M('GoodsAttributeValue');
		 				$m_attr  = M('Attribute');
		 				foreach ($attr_list as $attr_id => $value) {
		 					$data_goods_attr = [
		 						'goods_id' => $goods_id,
		 						'attribute_id' => $attr_id,
		 						// 在前台是否作为可选项
		 						'option' => in_array($attr_id,I('post.is_option',[])) ? 1 : 0,
		 					];
		 					// 插入商品-属性关联表，获取插入后生成id
		 				   $g_a_id = $m_goods_attr->add($data_goods_attr);
		 				   // 获取属性输入类型以确定插入的数据
		 				   $attr_type_id = $m_attr->getFieldByAttributeId($attr_id,'attribute_type_id');
		 				   $attr_type = $m_attr_type->getFieldByAttributeTypeId($attr_type_id,'title');
	 				// 根据属性的输入类型判断插入g_a_value表的数据格式
	 				switch ($attr_type) {
	 				   	case 'text':
	 				   		$data_g_a_v = [
	 				   			'goods_attribute_id' => $g_a_id,
	 				   			'value' => $value,
	 				   		];
	 				   		$m_goods_attr_value->add($data_g_a_v);
	 				   		break;
	 				   	case 'select':
	 				   		$data_g_a_v = [
	 				   			'goods_attribute_id' => $g_a_id,
	 				   			'attribute_value_id' => $value,
	 				   		];
	 				   		$m_goods_attr_value->add($data_g_a_v);
	 				   		break;
	 				   	case 'select-multiple':	
	 				   		$data_g_a_v_list = array_map(function($v) use($g_a_id){
	 				   			$data['goods_attribute_id'] = $g_a_id;
	 				   			$data['attribute_value_id'] = $v;
	 				   			return $data;
	 				   		}, $value);
	 				   		$m_goods_attr_value->addAll($data_g_a_v_list);
	 				   		break;
	 				   }
	 				}
			
			//5. 添加优惠价格
				$m_price_special = M('PriceSpecial');
				$rule = [
					['date_start','strtotime',3,'function'],
					['date_end','strtotime',3,'function'],
				];
				// 遍历价格优惠数组，添加goods_id;
				$price_special_list = I('post.price_special');
				foreach ($price_special_list as $price) {
					 $price['goods_id'] = $goods_id;
					 $m_price_special->auto($rule)->create($price);
					 $m_price_special->add();
				}
	 			$this->redirect('Back/Shop/list',[],0);
		 		  } 
		 		}else{		 			
		 			$this->error('添加商品失败,原因:'.$m_goods->getError(),U('Back/Shop/add'),3);
		 		}
		 	}else{
		 		// 展示表单
		 		$this->assign('tax_list',M('Tax')->select());
		 		$this->assign('stock_status_list',M('StockStatus')->select());
		 		$this->assign('length_unit_list',M('LengthUnit')->select());
		 		$this->assign('weight_unit_list',M('WeightUnit')->select());
		 		$this->assign('category_list',D('Category')->getTree());
		 		$brand_list =[
		 			['brand_id'=>1,'title'=>'品牌1'],
		 			['brand_id'=>2,'title'=>'品牌2'],
		 		];
		 		$this->assign('brand_list',$brand_list);
		 		// 获取商品类型数据
		 		$this->assign('goods_type_list',M('GoodsType')->select());
		 		// 商品属性
		 		$this->assign('goods_type',M('GoodsType')->select());
		 		$this->display();
		 	}
		 }
	  

		/**
		 * 图片上传处理
		 * @return [type] [description]
		 */
		public function imageUploadAction()
		{
			$upload = new Upload();// 实例化上传类    
			$upload->maxSize = 3145728 ;// 设置附件上传大小 
			$upload->exts = array('jpg', 'gif', 'png', 'jpeg');
			$upload->rootPath  = "./Uploads/Back/Goods/";
			$info = $upload->uploadOne($_FILES['image_ajax']);
			if ($info) {
				// 制作缩略图
				$t_img = new Image();
				$t_img->open($upload->rootPath.$info['savepath'].$info['savename']);
				//指定缩略图存放路径
				$thumb_rootpath = "./Uploads/Back/Goods/";
				$thumb_savepath = 'thumb/'.$info['savepath'];
				$thumb_savename = $info['savename'];
				// 判断目录是否存在
				if (!is_dir($thumb_rootpath.$thumb_savepath)) {
					mkdir($thumb_rootpath.$thumb_savepath,755,true);
				}
				//
				$t_img->thumb(40,40,Image::IMAGE_THUMB_SCALE)
					  ->save($thumb_rootpath.$thumb_savepath.$thumb_savename);
				$this->ajaxReturn([
					'error'=>'0',
					'image'=> $info['savepath'].$info['savename'],
					'image_thumb'=> $thumb_savepath.$thumb_savename,
					]);
			}else{
				$this->ajaxReturn(['error'=>'1']);
			}
		}
		/**
		 * 商品相册上传
		 * @return [type] [description]
		 */
		public function galleryUploadAction()
		{
			$upload = new Upload();// 实例化上传类    
			$upload->maxSize = 3145728 ;// 设置附件上传大小 
			$upload->exts = array('jpg', 'gif', 'png', 'jpeg');
			$upload->rootPath  = "./Uploads/Back/Goods/Gallery/";
			$info = $upload->uploadOne($_FILES['gallery']);
			if ($info) {
				// 制作缩略图
				$t_img = new Image();
				$t_img->open($upload->rootPath.$info['savepath'].$info['savename']);
				//指定缩略图存放路径
				$thumb_rootpath = "./Uploads/Back/Goods/Gallery/";
				$thumb_savepath = $info['savepath'];
				$thumb_savename = $info['savename'];
				// 判断目录是否存在
				if (!is_dir($thumb_rootpath.$thumb_savepath)) {
					mkdir($thumb_rootpath.$thumb_savepath,755,true);
				}
				//生成相册图3张
				$t_img->thumb(800,800,Image::IMAGE_THUMB_SCALE)
					  ->save($thumb_rootpath.$thumb_savepath.'big_'.$thumb_savename); //大图
			    $t_img->thumb(340,340,Image::IMAGE_THUMB_SCALE)
			  			->save($thumb_rootpath.$thumb_savepath.'medium_'.$thumb_savename);
			  	$t_img->thumb(60,60,Image::IMAGE_THUMB_SCALE)
					  ->save($thumb_rootpath.$thumb_savepath.'small_'.$thumb_savename);
				$this->ajaxReturn([
					'error'=>'0',
					'image'=> $info['savepath'].$info['savename'],
					'image_big'=> $thumb_savepath.'big_'.$thumb_savename,
					'image_medium'=> $thumb_savepath.'medium_'.$thumb_savename,
					'image_small'=> $thumb_savepath.'small_'.$thumb_savename,
					]);
			}else{
				$this->ajaxReturn(['error'=>'1']);
			}
		}
		/**
		 * 处理添加商品属性时，返回的数据[html]
		 * @return [type] [description]
		 */
		public function ajaxReturnAction($goods_type_id)
		{
			$m_attr = M('Attribute');
			// 获取属性列表
			$attr_list = $m_attr
					   ->alias('a')
					   ->field('a.*,at.title attr_input_type')
					   ->join('left join __ATTRIBUTE_TYPE__ as at using(attribute_type_id)') 
					   ->where(['goods_type_id' => $goods_type_id])
					   ->order('a.sort_number')
					   ->group('a.attribute_id')
					   ->select();
			// 获取属性的可选值
			$m_attr_value = M('AttributeValue');
			foreach ($attr_list as &$attr) {
				if(in_array($attr['attr_input_type'],['select','select-multiple'])) {
					$attr['option_list'] = $m_attr_value
										->where(['attribute_id'=>$attr['attribute_id']])
										->select();
				}
			}
			// dump($attr_list);
			// die;
			$this->assign('attr_list',$attr_list);
			$this->display();
		}
		/**
		 * 商品选项管理
		 * @return [type] [description]
		 */
		public function optionAction($id=0)
		{
			if (IS_POST) {
				$option_list = I('post.option');
				$m_gav = M('GoodsAttributeValue');
				foreach ($option_list as $key => $data) {
					$data['goods_attribute_value_id'] = $key;
					$m_gav->save($data);
				}
				// 获取goods_id
				$row = $m_gav
					   ->alias('gav')
					   ->field('ga.goods_id')
					   ->join('left join __GOODS_ATTRIBUTE__ ga using(goods_attribute_id)')
					   ->where(['goods_attribute_value_id'=>$key])
					   ->find();
				$this->redirect('Back/Shop/option',['id'=>$row['goods_id']],0);
			}else{						
				$m_goods_attr = M('GoodsAttribute');
				$option_attr_list = $m_goods_attr
							->alias('gt')
							->field('a.title a_title,a.attribute_id,gav.quantity,group_concat(av.value,\'-\',gav.goods_attribute_value_id,\'-\',gav.quantity,\'-\',gav.price_operate,\'-\',gav.price_drift,\'-\',gav.status) a_value_id')
							->join('left join __ATTRIBUTE__ a using(attribute_id)')
							->join('left join __GOODS_ATTRIBUTE_VALUE__ gav using(goods_attribute_id)')
							->join('left join __ATTRIBUTE_VALUE__ av using(attribute_value_id)')
							->where(['option'=>'1','goods_id'=>$id])
							// ->fetchSql()
							->group('a.attribute_id')
							->select();
				// 处理属性值-属性值id数据
				foreach ($option_attr_list as & $option) {
					$option_value_list = array_map(function($v){
						list($option_value['title'],$option_value['id'],$option_value['quantity'],$option_value['price_operate'],$option_value['price_drift'],$option_value['status'])=explode('-',$v);
						return $option_value;
					}, explode(',',$option['a_value_id']));
					$option['option_list'] =$option_value_list;
				}
				// dump($option_attr_list);
				// die;
				$this->assign('option_attr_list',$option_attr_list);
				$this->display();
			}
		}

		/**
		 * 逻辑删除商品
		 * @return [type] [description]
		 */
		public function removeAction()
		{
			$remove_list = I('post.selected');
			$m_goods = M('Goods');
			foreach ($remove_list as $rm_goods_id) {
				$m_goods->where(['goods_id'=>$rm_goods_id])
						->save(['is_deleted'=>1]);
			}
			$this->redirect('Back/Shop/list',[],0);
		}
		/**
		 * 更新商品信息		
		 * @return [type] [description]
		 */
		public function editAction($goods_id)
		{
			echo '商品更新';
		}

	}
 ?>