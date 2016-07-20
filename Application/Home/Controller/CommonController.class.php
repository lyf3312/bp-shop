<?php 
	
	namespace Home\Controller;
	use Think\Controller;

	/**
	* 前台通用控制器
	*/
	class CommonController extends Controller
	{
		/**
		 * 继承父类构造函数
		 */
		public function __construct()
		{
			parent::__construct();
			$this->initNavCategory();

		}
		/**
		 * 初始化导航的分类数据
		 * @return [type] [description]
		 */				
		protected function initNavCategory()
		{
			$m_category = D('Category');
			$category_nested = $m_category->getNested();
			$this->assign('category_nested',$category_nested);
		}
	}
 ?>