<?php 

	namespace Back\Model;
	use Think\Model;

	/**
	* BP_category分类模型
	*/
	class CategoryModel extends Model
	{
		
		protected $_validate = [
			['title','4,20','分类名称长度必须在4~20位之间',1,'length'],
			['sort_number','number','排序值必须为数字',1],
		];
		protected $_auto = [
			['image','chkimage',1,'callback'],

		];
		/**
		 * 自动验证中图片类型验证
		 * @return [type] [description]
		 */
		protected function chkimage()
		{
		   $ext_array = ['jpg','jpeg','png','gif'];
		   $img = $_POST['image'] = $_FILES['image'];
		   if ($img['error'] == 4 ){ 
		   		$this->error =  '图片必须上传';
		   }else{
		   		$ext = substr(strrchr($img['type'], '/'),1);
		   		if (!in_array($ext, $ext_array)) {
		   		    $this->error = '图片类型错误，仅支持jpg、jpeg、png、gif类型的图片';
		   		}else{
		   			return true;
		   		}
		   }
		}
		/**
		 * 获取全部分类数据，及各个类别的深度，最后存入文件缓存
		 * @return [type] [description]
		 */
		public function getTree()
		{
			// 开启文件缓存
			S(['type'=>'File']);
			// 判断缓存是否已存在
			if (!($category_tree_list = S('category_list_0'))) {
				// 获取数据
				$list = $this
						->order('sort_number')
						->select();
				// 设置缓存
				$category_tree_list = $this->Tree($list);
				S('category_list_0',$category_tree_list);
			} 
			return $category_tree_list;
		}

		/**
		 * 生成数状结构的数据
		 */
		public function Tree($list,$parent_id=0,$deep=0)
		{
			static $category_list =[];
			foreach ($list as $category) {
				if ($category['parent_id'] == $parent_id) {
					// 存在子分类
					$category['deep'] = $deep;
					$category_list[] = $category;
					$this->Tree($list,$category['category_id'],$deep+1);
				}
			}
			return $category_list;
		}
	}
 ?>