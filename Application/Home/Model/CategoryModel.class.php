<?php 

	namespace Home\Model;
	use Think\Model;


	/**
	* 前台通用模型
	*/
	class CategoryModel extends Model
	{
		/**
		 * 获取网状结构的数据
		 * @return [type] [description]
		 */					
		public function getNested()
		{
			// 启用文件缓存
			S(['type'=>'File']);
			$key ='category_nested_0'; 
			if (!($category_nested_list = S($key))) {
				// 不存在文件缓存
				$list = $this
						  ->where(['is_used' => 1])
						  ->order('sort_number')
						  ->select();
				// 生成缓存
				$category_nested_list = $this->nested($list);
				S($key,$category_nested_list);
			}
			return $category_nested_list;
		}
		/**
		 * 生成网状结构数据
		 * @return [type] [description]
		 */
		protected function nested($list,$parent_id=0)
		{
			$category_nested = [];
			foreach ($list as $category) {
				// 顶层分类且不可用，直接跳过
				if ($category['is_used'] ==0 && ($category['parent_id']==0)) {
					 continue;
				}
				if ($category['parent_id'] == $parent_id) {
   					//存在子分类
   					$category['nested'] = $this->nested($list,$category['category_id']);
   					$category_nested[] = $category;
				}
			}
			return $category_nested;
		}
		/**
		 * 获取自身和父分类的数据
		 * @return [type] [description]
		 */
		public function getSelfAndParents($category_id)
		{
			$self = $this->find($category_id);
			return array_merge([$self],$this->getParents($category_id));
		}	
		/**
		 * 获取父分类数据
		 * @return [type] [description]
		 */
		public function getParents($category_id)
		{
			static $parents = [];
			$parent_id = $this->getFieldByCategoryId($category_id,'parent_id');
			if ($parent_id != '0') {
				// 存在父级分类,查找存于parents中
				$parent = $this->find($parent_id);
				$parents[] = $parent;
				// 递归
				$this->getParents($parent['category_id']);
			}
			return $parents;
		}
	}
 ?>